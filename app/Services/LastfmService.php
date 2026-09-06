<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LastfmService
{
    protected $apiKey;
    protected $baseUrl = 'https://ws.audioscrobbler.com/2.0/';

    public function __construct()
    {
        $this->apiKey = config('services.lastfm.api_key');
    }

    /**
     * Fetch Top Artists directly from Last.fm (no cache)
     *
     * @param string $period overall|7day|1month|3month|6month|12month
     */
    public function getTopArtistsDirect($username, $limit = 8, $period = 'overall')
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopArtists',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
            'period' => $period,
        ]);

        $response = Http::get($url);

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        
        if (!isset($data['topartists']['artist'])) {
            return [];
        }

        $result = [];
        foreach ($data['topartists']['artist'] as $artist) {
            $image = $this->getImage($artist['image'] ?? []);

            // Last.fm never gives real artist photos anymore — fall back
            // to Deezer for an actual picture.
            if (!$image || $this->isPlaceholderImage($image)) {
                $image = $this->getArtistImage($artist['name']);
            }

            $result[] = [
                'name' => $artist['name'],
                'image' => $image,
                'playcount' => (int) ($artist['playcount'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Fetch Top Tracks directly from Last.fm (no cache)
     *
     * @param string $period overall|7day|1month|3month|6month|12month
     */
    public function getTopTracksDirect($username, $limit = 8, $period = 'overall')
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopTracks',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
            'period' => $period,
        ]);

        $response = Http::get($url);

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        
        if (!isset($data['toptracks']['track'])) {
            return [];
        }

        $result = [];
        foreach ($data['toptracks']['track'] as $track) {
            $trackArtist = $this->extractArtistName($track['artist'] ?? null);

            // Try to get image from track first, if not found (or it's just
            // Last.fm's placeholder star, which is now the norm for track
            // images) fall back to the album art instead.
            $image = $this->getImage($track['image'] ?? []);

            if ((!$image || $this->isPlaceholderImage($image)) && isset($track['album']['image'])) {
                $image = $this->getImage($track['album']['image']);
            }

            // user.getTopTracks doesn't actually include album data at all
            // (unlike user.getRecentTracks), so the fallback above almost
            // never fires. Look up the real album art via track.getInfo.
            if (!$image || $this->isPlaceholderImage($image)) {
                $image = $this->getTrackAlbumArt($trackArtist, $track['name']);
            }
            
            $result[] = [
                'name' => $track['name'],
                'artist' => $trackArtist,
                'image' => $image,
                'playcount' => (int) ($track['playcount'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Fetch Top Albums directly from Last.fm (no cache)
     *
     * @param string $period overall|7day|1month|3month|6month|12month
     */
    public function getTopAlbumsDirect($username, $limit = 8, $period = 'overall')
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopAlbums',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
            'period' => $period,
        ]);

        $response = Http::get($url);

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        
        if (!isset($data['topalbums']['album'])) {
            return [];
        }

        $result = [];
        foreach ($data['topalbums']['album'] as $album) {
            $result[] = [
                'name' => $album['name'],
                'artist' => $this->extractArtistName($album['artist'] ?? null),
                'image' => $this->getImage($album['image'] ?? []),
                'playcount' => (int) ($album['playcount'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get the user's currently playing track
     */
    public function getNowPlaying($username)
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getRecentTracks',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => 1,
        ]);

        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        
        if (!isset($data['recenttracks']['track'][0])) {
            return null;
        }

        $track = $data['recenttracks']['track'][0];
        $isNowPlaying = isset($track['@attr']['nowplaying']) && $track['@attr']['nowplaying'] == 'true';
        
        // Try to get image from track, fallback to album (track images are
        // now almost always just Last.fm's placeholder star)
        $image = $this->getImage($track['image'] ?? []);
        if ((!$image || $this->isPlaceholderImage($image)) && isset($track['album']['image'])) {
            $image = $this->getImage($track['album']['image']);
        }
        
        return [
            'name' => $track['name'] ?? 'Unknown',
            'artist' => $track['artist']['#text'] ?? 'Unknown Artist',
            'album' => $track['album']['#text'] ?? '',
            'is_now_playing' => $isNowPlaying,
            'image' => $image,
        ];
    }

    /**
     * Extract an artist name from a Last.fm 'artist' field, which is
     * normally an object ({"name": "...", ...}) but occasionally comes
     * back as a plain string depending on the endpoint/edge case. Handles
     * both so "Unknown Artist" only shows when the data is truly missing.
     */
    private function extractArtistName($artistField): string
    {
        if (is_array($artistField) && !empty($artistField['name'])) {
            return $artistField['name'];
        }

        if (is_string($artistField) && $artistField !== '') {
            return $artistField;
        }

        return 'Unknown Artist';
    }

    /**
     * Get a real artist photo from Deezer's public search API (free, no
     * key required), since Last.fm intentionally no longer serves real
     * artist images through ANY of its own endpoints (not just top
     * artists — artist.getInfo is the same). Cached for a week since
     * artist photos rarely change, and the cache key is just the artist
     * name, so it's shared across every user of the app.
     */
    public function getArtistImage(string $artistName): ?string
    {
        if (empty($artistName)) {
            return null;
        }

        $cacheKey = 'artist_image:' . md5(strtolower($artistName));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($artistName) {
            try {
                $response = Http::timeout(5)->get('https://api.deezer.com/search/artist', [
                    'q' => $artistName,
                    'limit' => 1,
                ]);

                if ($response->failed()) {
                    return null;
                }

                $data = $response->json();

                return $data['data'][0]['picture_medium']
                    ?? $data['data'][0]['picture']
                    ?? null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Get real album art for a track via Last.fm's track.getInfo.
     * user.getTopTracks (unlike user.getRecentTracks) doesn't include
     * album data in its response at all, so there's nothing to fall
     * back to without this extra lookup. Cached for a week per
     * artist+track pair, shared across all users.
     */
    public function getTrackAlbumArt(string $artistName, string $trackName): ?string
    {
        if (empty($artistName) || empty($trackName)) {
            return null;
        }

        $cacheKey = 'track_album_art:' . md5(strtolower($artistName . '|' . $trackName));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($artistName, $trackName) {
            try {
                $url = $this->baseUrl . '?' . http_build_query([
                    'method' => 'track.getInfo',
                    'artist' => $artistName,
                    'track' => $trackName,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

                $response = Http::timeout(5)->get($url);

                if ($response->failed()) {
                    return null;
                }

                $data = $response->json();
                $image = $this->getImage($data['track']['album']['image'] ?? []);

                return ($image && !$this->isPlaceholderImage($image)) ? $image : null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Extract the largest available image from Last.fm response
     * Forces all images to use 300x300 size
     */
    private function getImage($images)
    {
        if (empty($images)) {
            return null;
        }

        // If it's a string URL, normalize it
        if (is_string($images) && filter_var($images, FILTER_VALIDATE_URL)) {
            return $this->normalizeImageUrl($images);
        }

        // If it's a simple array of URLs
        if (isset($images[0]) && is_string($images[0]) && filter_var($images[0], FILTER_VALIDATE_URL)) {
            return $this->normalizeImageUrl($images[0]);
        }

        // Handle Last.fm format with size keys
        if (is_array($images)) {
            // Try to get the largest image first
            $prioritySizes = ['extralarge', 'large', 'medium', 'small'];
            $foundImage = null;
            
            foreach ($prioritySizes as $sizeKey) {
                // Check for keyed array format
                if (isset($images[$sizeKey]) && !empty($images[$sizeKey])) {
                    $foundImage = $images[$sizeKey];
                    return $this->normalizeImageUrl($foundImage);
                }
                
                // Check for indexed array with size keys
                foreach ($images as $img) {
                    if (is_array($img) && isset($img['size']) && $img['size'] === $sizeKey && !empty($img['#text'])) {
                        $foundImage = $img['#text'];
                        return $this->normalizeImageUrl($foundImage);
                    }
                }
            }
            
            // If we found any image, normalize it
            if ($foundImage) {
                return $this->normalizeImageUrl($foundImage);
            }
            
            // Ultimate fallback: return first valid URL from the array
            foreach ($images as $img) {
                if (is_array($img) && isset($img['#text']) && !empty($img['#text'])) {
                    return $this->normalizeImageUrl($img['#text']);
                }
                if (is_string($img) && !empty($img) && filter_var($img, FILTER_VALIDATE_URL)) {
                    return $this->normalizeImageUrl($img);
                }
            }
        }

        return null;
    }

    /**
     * Normalize Last.fm image URL to always use 300x300
     * This is the key function that forces all images to the same size
     */
    private function normalizeImageUrl($url)
    {
        if (empty($url)) {
            return null;
        }
        
        // Replace any size in Last.fm URLs with 300x300
        // This handles ALL size formats:
        // /i/u/34s/     -> /i/u/300x300/
        // /i/u/174s/    -> /i/u/300x300/
        // /i/u/64s/     -> /i/u/300x300/
        // /i/u/300x300/ -> /i/u/300x300/ (already correct)
        // /i/u/500x500/ -> /i/u/300x300/
        //
        // NOTE: the real Last.fm CDN domain is "lastfm.freetls.fastly.net"
        // (no "-img" segment). A typo here previously meant this check
        // never matched, so images passed through unresized.
        if (strpos($url, 'lastfm.freetls.fastly.net/i/u/') !== false) {
            // Replace /i/u/XXXs/ with /i/u/300x300/
            $url = preg_replace('/\/i\/u\/\d+s\//', '/i/u/300x300/', $url);
            // Replace /i/u/XXXxXXX/ with /i/u/300x300/
            $url = preg_replace('/\/i\/u\/\d+x\d+\//', '/i/u/300x300/', $url);
        }
        
        return $url;
    }

    /**
     * Check if an image URL is the generic Last.fm placeholder
     */
    public function isPlaceholderImage($url)
    {
        if (empty($url)) {
            return true;
        }
        
        // Common Last.fm placeholder patterns
        $placeholders = [
            '2a96cbd8b46e442fc41c2b86b821562f', // Common Last.fm placeholder
            'avatar_default',                   // Default avatar
            'default_avatar',                  // Another default
            'noimage',                        // No image
            'placeholder',                    // Placeholder
        ];
        
        foreach ($placeholders as $placeholder) {
            if (strpos($url, $placeholder) !== false) {
                return true;
            }
        }
        
        return false;
    }
}