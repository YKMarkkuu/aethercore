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
        
        return view('profile', compact('user', 'feedPosts'));
    }

    /**
     * Display any user's profile by ID.
     */
    public function show(User $user)
    {
        $user->load(['profile', 'posts']);
        
        // Get all friend IDs (sent + received)
        $sentIds = DB::table('friendships')
            ->where('user_id', auth()->id())
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->toArray();

        $receivedIds = DB::table('friendships')
            ->where('friend_id', auth()->id())
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        $friendIds = array_unique(array_merge($sentIds, $receivedIds));
        
        $feedPosts = Post::whereIn('user_id', $friendIds)
                        ->orWhere('user_id', auth()->id())
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->with('user')
                        ->get();
        
        return view('profile', compact('user', 'feedPosts'));
    }

    // ===== LARAVEL DEFAULT METHODS (from Breeze) - REDIRECTED =====
    
    /**
     * Display the user's profile form.
     * Redirected to profile page instead.
     */
    public function edit(Request $request): RedirectResponse
    {
        return redirect()->route('profile.index');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update display name (name column)
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        // Update username (if provided and unique)
        if ($request->has('username') && $request->username !== $user->username) {
            $request->validate([
                'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            ]);
            $user->username = $request->username;
        }

        // Update email
        if ($request->has('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }

        $user->save();

        // Update profile (bio, display_name, location, etc.)
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

    /**
     * Update the user's profile (avatar, banner, display_name, bio)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        
        if (!$profile) {
            $profile = new \App\Models\Profile();
            $profile->user_id = $user->id;
        }
        
        // Update display name
        if ($request->has('display_name')) {
            $profile->display_name = $request->display_name;
        }
        
        // Update bio
        if ($request->has('bio')) {
            $profile->bio = $request->bio;
        }
        
        // Update location (commented out until column exists)
        // if ($request->has('location')) {
        //     $profile->location = $request->location;
        // }
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar = $avatarPath;
        }
        
        // Handle banner upload
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
            $profile->banner = $bannerPath;
        }
        
        $profile->save();
        
        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
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