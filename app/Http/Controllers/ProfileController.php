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
     * Valid Last.fm chart periods, used to guard against garbage query strings.
     */
    protected const VALID_PERIODS = ['overall', '7day', '1month', '3month', '6month', '12month'];

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
        // Period always comes from the profile owner's saved preference,
        // never a visitor's query string — see fetchLastFmData().
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
        // Period always comes from the profile owner's saved preference,
        // never a visitor's query string — see fetchLastFmData().
        $lastfmData = $this->fetchLastFmData($user);

        $user->lastfm_data = $lastfmData;
        
        return view('profile', compact('user', 'feedPosts'));
    }

    /**
     * Let the profile OWNER change which Last.fm chart period shows on
     * their profile — this applies to everyone who views it, not just
     * the owner. The dropdown that posts here is only ever rendered in
     * the blade template for auth()->id() === $user->id, so a visitor
     * has no UI path to this route, but it's also behind the auth
     * middleware in routes/web.php as a second layer of protection.
     */
    public function updateStatsPeriod(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
        ]);

        $period = in_array($request->period, self::VALID_PERIODS, true)
            ? $request->period
            : 'overall';

        $user = auth()->user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = new \App\Models\Profile();
            $profile->user_id = $user->id;
        }

        $profile->stats_period = $period;
        $profile->save();

        return redirect()->back();
    }

    /**
     * Fetch Last.fm data for a user.
     *
     * NOTE: LastfmService already fully normalizes each item (flattens
     * artist to a plain string, resolves the final 300x300 image URL,
     * falls back to album art for tracks without their own image, and
     * includes playcount). This method used to re-process that output
     * with its own duplicate extractImage()/artist-name logic, which
     * assumed the raw un-flattened Last.fm shape — e.g. doing
     * $album['artist']['name'] on a value that was already just a
     * string, which silently always fell through to 'Unknown Artist'.
     * We now just pass the service's output straight through.
     *
     * The period is read from $user->profile->stats_period — i.e. the
     * PROFILE OWNER'S saved choice — regardless of who is viewing the
     * page, so everyone sees the same window the owner picked.
     */
    protected function fetchLastFmData($user)
    {
        $period = $user->profile->stats_period ?? 'overall';
        if (!in_array($period, self::VALID_PERIODS, true)) {
            $period = 'overall';
        }

        $lastfmData = [
            'top_artists' => [],
            'top_songs' => [],
            'top_albums' => [],
            'now_playing' => null,
            'period' => $period,
        ];

        if (!$user->lastfm_username) {
            return $lastfmData;
        }

        try {
            $lastfm = new LastfmService();

            $artists = $lastfm->getTopArtistsDirect($user->lastfm_username, 8, $period);
            if ($artists && is_array($artists)) {
                $lastfmData['top_artists'] = array_slice($artists, 0, 8);
            }

            $tracks = $lastfm->getTopTracksDirect($user->lastfm_username, 8, $period);
            if ($tracks && is_array($tracks)) {
                $lastfmData['top_songs'] = array_slice($tracks, 0, 8);
            }

            $albums = $lastfm->getTopAlbumsDirect($user->lastfm_username, 8, $period);
            if ($albums && is_array($albums)) {
                $lastfmData['top_albums'] = array_slice($albums, 0, 8);
            }

            // Now playing is always "right now", so it isn't affected by period
            $nowPlaying = $lastfm->getNowPlaying($user->lastfm_username);
            if ($nowPlaying && !empty($nowPlaying['is_now_playing'])) {
                $lastfmData['now_playing'] = [
                    'name' => $nowPlaying['name'] ?? 'Unknown Track',
                    'artist' => $nowPlaying['artist'] ?? 'Unknown Artist',
                    'image' => $nowPlaying['image'] ?? null,
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