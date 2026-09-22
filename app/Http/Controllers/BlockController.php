<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlockController extends Controller
{
    /**
     * Block a user. Blocking always implies unfriending — there is no
     * valid state where two users are simultaneously friends and
     * blocked, so any existing friendship row (either direction) is
     * removed at the same time.
     */
    public function store($userId)
    {
        $authId = Auth::id();

        if ((int) $userId === $authId) {
            return back()->withErrors(['block' => 'You cannot block yourself.']);
        }

        Block::firstOrCreate([
            'blocker_id' => $authId,
            'blocked_id' => $userId,
        ]);

        DB::table('friendships')
            ->where(function ($q) use ($authId, $userId) {
                $q->where('user_id', $authId)->where('friend_id', $userId);
            })
            ->orWhere(function ($q) use ($authId, $userId) {
                $q->where('user_id', $userId)->where('friend_id', $authId);
            })
            ->delete();

        return back()->with('success', 'User blocked.');
    }

    public function destroy($userId)
    {
        Block::where('blocker_id', Auth::id())
            ->where('blocked_id', $userId)
            ->delete();

        return back()->with('success', 'User unblocked.');
    }
}
