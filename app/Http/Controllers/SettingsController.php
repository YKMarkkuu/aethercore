<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // 👈 ADD THIS

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'display_name' => 'sometimes|string|max:255',
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'sometimes|string|min:8|confirmed',
        ]);
        
        if ($request->has('username')) {
            $user->username = $request->username;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }
        
        if ($request->has('display_name')) {
            $profile = $user->profile ?? new \App\Models\Profile(['user_id' => $user->id]);
            $profile->display_name = $request->display_name;
            $profile->save();
        }
        
        if ($request->has('new_password')) {
            $user->password = Hash::make($request->new_password);
        }
        
        $user->save();
        
        return back()->with('success', 'Account updated successfully!');
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:aethercore,midnight,daylight,retro',
        ]);
        
        $user = Auth::user();
        $user->theme = $request->theme;
        $user->save();
        
        return back()->with('success', 'Theme updated!');
    }

    public function connectLastfm(Request $request)
    {
        $request->validate([
            'lastfm_username' => 'required|string|max:255',
        ]);
        
        $user = Auth::user();
        $user->lastfm_username = $request->lastfm_username;
        $user->save();
        
        // Clear old cache
        Cache::forget('lastfm_top_artists_' . $user->id);
        Cache::forget('lastfm_top_tracks_' . $user->id);
        Cache::forget('lastfm_top_albums_' . $user->id);
        
        return back()->with('success', 'Last.fm connected successfully!');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        
        Auth::logout();
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Account deleted successfully.');
    }
}