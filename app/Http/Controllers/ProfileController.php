<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Services\LastfmService;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    // ===== AETHERCORE CUSTOM METHODS =====
    
    /**
     * Display the logged-in user's profile.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all friend IDs (sent + received)
        $sentIds = DB::table('friendships')
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->toArray();

        $receivedIds = DB::table('friendships')
            ->where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        $friendIds = array_unique(array_merge($sentIds, $receivedIds));
        
        $feedPosts = Post::whereIn('user_id', $friendIds)
                        ->orWhere('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->with('user')
                        ->get();

        // ===== LAST.FM DATA =====
        $lastfmData = $this->fetchLastFmData($user);

        $user->lastfm_data = $lastfmData;

        return view('profile', compact('user', 'feedPosts'));
    }

    /**
     * Display any user's profile by ID.
     */
    public function show(User $user)
    {
        $user->load(['profile', 'posts']);
        
        // Get the viewed user's friend IDs (NOT the logged-in user's)
        $sentIds = DB::table('friendships')
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->toArray();

        $receivedIds = DB::table('friendships')
            ->where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        $friendIds = array_unique(array_merge($sentIds, $receivedIds));
        
        $feedPosts = Post::whereIn('user_id', $friendIds)
                        ->orWhere('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->with('user')
                        ->get();

        // ===== LAST.FM DATA =====
        $lastfmData = $this->fetchLastFmData($user);

        $user->lastfm_data = $lastfmData;
        
        return view('profile', compact('user', 'feedPosts'));
    }

    /**
     * Fetch Last.fm data for a user
     */
    protected function fetchLastFmData($user)
    {
        $lastfmData = [
            'top_artists' => [],
            'top_songs' => [],
            'top_albums' => [],
            'now_playing' => null,
        ];

        if (!$user->lastfm_username) {
            return $lastfmData;
        }

        try {
            $lastfm = new LastfmService();
            
            // Fetch artists
            $artists = $lastfm->getTopArtistsDirect($user->lastfm_username, 8);
            if ($artists && is_array($artists)) {
                $lastfmData['top_artists'] = array_slice(array_map(function($artist) {
                    return [
                        'name' => $artist['name'] ?? 'Unknown Artist',
                        'image' => $this->extractImage($artist['image'] ?? [])
                    ];
                }, $artists), 0, 8);
            }
            
            // Fetch tracks - with better image extraction
            $tracks = $lastfm->getTopTracksDirect($user->lastfm_username, 8);
            if ($tracks && is_array($tracks)) {
                $lastfmData['top_songs'] = array_slice(array_map(function($track) {
                    // Try to get album image if track image is empty
                    $image = $this->extractImage($track['image'] ?? []);
                    
                    // If no track image, try to get album art from the album data
                    if (!$image && isset($track['album']) && isset($track['album']['image'])) {
                        $image = $this->extractImage($track['album']['image']);
                    }
                    
                    return [
                        'name' => $track['name'] ?? 'Unknown Track',
                        'artist' => $track['artist']['name'] ?? 'Unknown Artist',
                        'image' => $image
                    ];
                }, $tracks), 0, 8);
            }
            
            // Fetch albums - with better image extraction
            $albums = $lastfm->getTopAlbumsDirect($user->lastfm_username, 8);
            if ($albums && is_array($albums)) {
                $lastfmData['top_albums'] = array_slice(array_map(function($album) {
                    return [
                        'name' => $album['name'] ?? 'Unknown Album',
                        'artist' => $album['artist']['name'] ?? 'Unknown Artist',
                        'image' => $this->extractImage($album['image'] ?? [])
                    ];
                }, $albums), 0, 8);
            }
            
            // Fetch now playing
            $nowPlaying = $lastfm->getNowPlaying($user->lastfm_username);
            if ($nowPlaying && isset($nowPlaying['is_now_playing']) && $nowPlaying['is_now_playing']) {
                $lastfmData['now_playing'] = [
                    'name' => $nowPlaying['name'] ?? 'Unknown Track',
                    'artist' => $nowPlaying['artist']['name'] ?? 'Unknown Artist',
                    'image' => $this->extractImage($nowPlaying['image'] ?? [])
                ];
            }
            
        } catch (\Exception $e) {
            // Silent fail - just return empty data
            \Log::warning('Last.fm fetch failed for user: ' . ($user->lastfm_username ?? 'unknown'), [
                'error' => $e->getMessage()
            ]);
        }

        return $lastfmData;
    }

    /**
     * Extract the best available image from Last.fm response.
     * Improved to handle all Last.fm image formats consistently.
     */
    protected function extractImage($images)
    {
        if (empty($images)) {
            return null;
        }

        // If it's a string URL, return it directly
        if (is_string($images) && filter_var($images, FILTER_VALIDATE_URL)) {
            return $images;
        }

        // If it's a simple array of URLs
        if (isset($images[0]) && is_string($images[0]) && filter_var($images[0], FILTER_VALIDATE_URL)) {
            return $images[0];
        }

        // Handle Last.fm format with size keys
        if (is_array($images)) {
            $prioritySizes = ['extralarge', 'large', 'medium', 'small'];
            
            foreach ($prioritySizes as $sizeKey) {
                // Check for keyed array format
                if (isset($images[$sizeKey]) && !empty($images[$sizeKey])) {
                    return $images[$sizeKey];
                }
                
                // Check for indexed array with size keys
                foreach ($images as $img) {
                    if (is_array($img) && isset($img['size']) && $img['size'] === $sizeKey && !empty($img['#text'])) {
                        return $img['#text'];
                    }
                }
            }
            
            // Fallback: return first valid URL from the array
            foreach ($images as $img) {
                if (is_array($img) && isset($img['#text']) && !empty($img['#text'])) {
                    return $img['#text'];
                }
                if (is_string($img) && !empty($img) && filter_var($img, FILTER_VALIDATE_URL)) {
                    return $img;
                }
            }
        }

        return null;
    }

    // ===== LARAVEL DEFAULT METHODS (from Breeze) - REDIRECTED =====
    
    public function edit(Request $request): RedirectResponse
    {
        return redirect()->route('profile.index');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('username') && $request->username !== $user->username) {
            $request->validate([
                'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            ]);
            $user->username = $request->username;
        }

        if ($request->has('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }

        $user->save();

        if ($request->has('bio') || $request->has('display_name') || $request->has('location')) {
            $profile = $user->profile;
            if (!$profile) {
                $profile = new \App\Models\Profile();
                $profile->user_id = $user->id;
            }
            
            if ($request->has('bio')) {
                $profile->bio = $request->bio;
            }
            if ($request->has('display_name')) {
                $profile->display_name = $request->display_name;
            }
            if ($request->has('location')) {
                $profile->location = $request->location;
            }
            
            $profile->save();
        }

        return redirect()->route('profile.index')->with('status', 'Profile updated successfully!');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        
        if (!$profile) {
            $profile = new \App\Models\Profile();
            $profile->user_id = $user->id;
        }
        
        if ($request->has('display_name')) {
            $profile->display_name = $request->display_name;
        }
        
        if ($request->has('bio')) {
            $profile->bio = $request->bio;
        }
        
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar = $avatarPath;
        }
        
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
            $profile->banner = $bannerPath;
        }
        
        $profile->save();
        
        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}