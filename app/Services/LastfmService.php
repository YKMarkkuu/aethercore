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
     */
    public function getTopArtistsDirect($username, $limit = 8)
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopArtists',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
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
            $result[] = [
                'name' => $artist['name'],
                'image' => $this->getImage($artist['image'] ?? []),
            ];
        }

        return $result;
    }

    /**
     * Fetch Top Tracks directly from Last.fm (no cache)
     */
    public function getTopTracksDirect($username, $limit = 8)
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopTracks',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
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
            // Try to get image from track first, if not found (or it's just
            // Last.fm's placeholder star, which is now the norm for track
            // images) fall back to the album art instead.
            $image = $this->getImage($track['image'] ?? []);

            if ((!$image || $this->isPlaceholderImage($image)) && isset($track['album']['image'])) {
                $image = $this->getImage($track['album']['image']);
            }
            
            $result[] = [
                'name' => $track['name'],
                'artist' => $track['artist']['name'] ?? 'Unknown Artist',
                'image' => $image,
            ];
        }

        return $result;
    }

    /**
     * Fetch Top Albums directly from Last.fm (no cache)
     */
    public function getTopAlbumsDirect($username, $limit = 8)
    {
        $url = $this->baseUrl . '?' . http_build_query([
            'method' => 'user.getTopAlbums',
            'user' => $username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
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
                'artist' => $album['artist']['name'] ?? 'Unknown Artist',
                'image' => $this->getImage($album['image'] ?? []),
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