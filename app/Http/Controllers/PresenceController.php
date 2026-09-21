<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{
    /**
     * Pinged every ~20s by presence.js while a tab is open. Always
     * proves the user is connected; only bumps last_active_at when the
     * client reports recent interaction, which drives auto-idle.
     */
    public function heartbeat(Request $request)
    {
        $user = Auth::user();

        $user->last_seen_at = now();
        if ($request->boolean('active')) {
            $user->last_active_at = now();
        }
        $user->save();

        return response()->json(['status' => $user->getEffectiveStatus()]);
    }

    /**
     * Fired via navigator.sendBeacon() on tab close/navigate-away, so a
     * closed tab flips to offline immediately instead of waiting out the
     * heartbeat timeout. Beacon requests can't read a response.
     */
    public function goOffline(Request $request)
    {
        $user = Auth::user();
        $user->last_seen_at = now()->subSeconds(User::ONLINE_TIMEOUT_SECONDS + 1);
        $user->save();

        return response()->json(['success' => true]);
    }
}