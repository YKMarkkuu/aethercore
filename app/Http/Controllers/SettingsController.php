<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    protected function profileFor($user)
    {
        $profile = $user->profile ?? new \App\Models\Profile(['user_id' => $user->id]);
        return $profile;
    }

    protected function respond(Request $request, string $message, string $redirectBack = null, array $extra = [])
    {
        if ($request->wantsJson()) {
            return response()->json(array_merge(['message' => $message, 'type' => 'success'], $extra));
        }

        return back()->with('success', $message);
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
            $profile = $this->profileFor($user);
            $profile->display_name = $request->display_name;
            $profile->save();
        }

        if ($request->has('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return $this->respond($request, 'Account updated successfully!', null, [
            'user' => [
                'username' => $user->username,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        ]);
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:aethercore,midnight,daylight,retro',
        ]);

        $user = Auth::user();
        $user->theme = $request->theme;
        $user->save();

        return $this->respond($request, 'Theme updated! Refresh to see it everywhere.');
    }

    public function connectLastfm(Request $request)
    {
        $request->validate([
            'lastfm_username' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->lastfm_username = $request->lastfm_username;
        $user->save();

        Cache::forget('lastfm_top_artists_' . $user->id);
        Cache::forget('lastfm_top_tracks_' . $user->id);
        Cache::forget('lastfm_top_albums_' . $user->id);

        return $this->respond($request, 'Last.fm connected successfully!');
    }

    /**
     * Privacy Controls (updates.txt Tier 2, #8). Three independent
     * settings, each an <select onchange="this.form.submit()"> in the
     * UI, each POSTing to this one endpoint — only the fields present
     * in the request are touched, so one dropdown's change never
     * clobbers the other two.
     */
    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'visibility' => 'sometimes|in:public,friends,private',
            'dm_permission' => 'sometimes|in:everyone,friends,nobody',
            'show_status_to' => 'sometimes|in:everyone,friends,nobody',
        ]);

        $profile = $this->profileFor(Auth::user());

        foreach (['visibility', 'dm_permission', 'show_status_to'] as $field) {
            if ($request->has($field)) {
                $profile->$field = $request->input($field);
            }
        }

        $profile->save();

        return $this->respond($request, 'Privacy settings updated!');
    }

    /**
     * Notification Preferences. Same one-endpoint-per-toggle pattern as
     * updatePrivacy() — each checkbox posts independently, so unrelated
     * preferences never get clobbered. Unchecked checkboxes simply
     * don't appear in the request, so we always write an explicit
     * true/false rather than relying on `has()`.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notify_email' => 'sometimes|boolean',
            'notify_friend_requests' => 'sometimes|boolean',
            'notify_messages' => 'sometimes|boolean',
            'notify_likes_comments' => 'sometimes|boolean',
        ]);

        $profile = $this->profileFor(Auth::user());

        foreach (['notify_email', 'notify_friend_requests', 'notify_messages', 'notify_likes_comments'] as $field) {
            $profile->$field = $request->boolean($field);
        }

        $profile->save();

        return $this->respond($request, 'Notification preferences updated!');
    }

    /**
     * Data Export (updates.txt Tier 2, #9 — DPA right of access).
     * Streams a JSON download; deliberately a GET link rather than a
     * form, so it behaves like a normal file download instead of
     * routing through the AJAX form engine.
     */
    public function exportData(Request $request)
    {
        $user = Auth::user();
        $user->load(['profile', 'posts']);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'joined_at' => $user->created_at?->toIso8601String(),
            ],
            'profile' => $user->profile ? [
                'display_name' => $user->profile->display_name,
                'bio' => $user->profile->bio,
                'location' => $user->profile->location,
                'visibility' => $user->profile->visibility,
                'dm_permission' => $user->profile->dm_permission,
                'show_status_to' => $user->profile->show_status_to,
            ] : null,
            'posts' => $user->posts->map(fn ($p) => [
                'id' => $p->id,
                'content' => $p->content,
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
            'friends' => $user->getFriends()->pluck('username')->values(),
        ];

        $filename = 'aethercore-data-export-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * Session/Device Management (updates.txt Tier 2, #10). Only
     * meaningful with SESSION_DRIVER=database — otherwise there's
     * nothing in a queryable table to list, and we say so plainly
     * rather than pretending the feature works.
     */
    public function sessionsPartial(Request $request)
    {
        return view('partials.settings-sessions-list', [
            'sessions' => $this->activeSessions(),
            'currentSessionId' => $request->session()->getId(),
            'sessionDriverSupported' => config('session.driver') === 'database',
        ]);
    }

    protected function activeSessions()
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get();
    }

    public function destroySession(Request $request, string $id)
    {
        if (config('session.driver') !== 'database') {
            return $this->respond($request, 'Session management is unavailable on this setup.');
        }

        if ($id === $request->session()->getId()) {
            return response()->json(['message' => "You can't log out your current session from here — use Log Out instead.", 'type' => 'error'], 422);
        }

        DB::table('sessions')->where('id', $id)->where('user_id', Auth::id())->delete();

        return $this->respond($request, 'That device has been logged out.');
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

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account deleted successfully.', 'type' => 'success', 'redirect' => '/']);
        }

        return redirect('/')->with('success', 'Account deleted successfully.');
    }
}