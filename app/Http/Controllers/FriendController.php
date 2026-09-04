<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    // Show friends page
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $friends = Auth::user()->getFriends();
        $requests = Auth::user()->friendRequests()->get();
        
        $users = User::where('id', '!=', Auth::id());
        if ($search) {
            $users->where('username', 'LIKE', '%' . $search . '%');
        }
        $users = $users->get();
        
        return view('friends', compact('friends', 'requests', 'users'));
    }

    // Send friend request
    public function sendRequest($userId)
    {
        $friend = User::findOrFail($userId);
        $user = Auth::user();
        
        // Can't befriend yourself
        if ($user->id === $friend->id) {
            return back()->with('error', 'You cannot befriend yourself.');
        }
        
        // Check if already friends
        if ($user->isFriendWith($friend->id)) {
            return back()->with('error', 'You are already friends.');
        }
        
        // Check if YOU already sent a request to them
        $sentRequest = DB::table('friendships')
            ->where('user_id', $user->id)
            ->where('friend_id', $friend->id)
            ->where('status', 'pending')
            ->exists();
        if ($sentRequest) {
            return back()->with('error', 'Friend request already sent.');
        }
        
        // Check if THEY already sent a request to YOU
        $receivedRequest = DB::table('friendships')
            ->where('user_id', $friend->id)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($receivedRequest) {
            return back()->with('error', 'They already sent you a friend request.');
        }
        
        // Create the friend request using direct DB insert
        DB::table('friendships')->insert([
            'user_id' => $user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return back()->with('success', 'Friend request sent!');
    }

    // Accept friend request
    public function acceptRequest($userId)
    {
        $user = Auth::user();
        
        // Find the pending friendship record
        $friendship = DB::table('friendships')
            ->where('user_id', $userId)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->first();
        
        if (!$friendship) {
            return back()->with('error', 'No pending friend request from this user.');
        }
        
        // Update the status using direct DB update
        DB::table('friendships')
            ->where('id', $friendship->id)
            ->update([
                'status' => 'accepted',
                'updated_at' => now(),
            ]);
        
        return back()->with('success', 'Friend request accepted!');
    }

    // Reject or unfriend
    public function rejectRequest($userId)
    {
        $user = Auth::user();
        
        // Check if there's a pending request from this user to you
        $pendingFromThem = DB::table('friendships')
            ->where('user_id', $userId)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($pendingFromThem) {
            DB::table('friendships')
                ->where('user_id', $userId)
                ->where('friend_id', $user->id)
                ->delete();
            return back()->with('success', 'Friend request declined.');
        }
        
        // Check if you have a pending request to them
        $pendingFromYou = DB::table('friendships')
            ->where('user_id', $user->id)
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->exists();
        if ($pendingFromYou) {
            DB::table('friendships')
                ->where('user_id', $user->id)
                ->where('friend_id', $userId)
                ->delete();
            return back()->with('success', 'Friend request cancelled.');
        }
        
        // Check if you're already friends
        $areFriends = DB::table('friendships')
            ->where(function($query) use ($user, $userId) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $userId)
                    ->where('status', 'accepted');
            })
            ->orWhere(function($query) use ($user, $userId) {
                $query->where('user_id', $userId)
                    ->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->exists();
        
        if ($areFriends) {
            DB::table('friendships')
                ->where(function($query) use ($user, $userId) {
                    $query->where('user_id', $user->id)
                        ->where('friend_id', $userId);
                })
                ->orWhere(function($query) use ($user, $userId) {
                    $query->where('user_id', $userId)
                        ->where('friend_id', $user->id);
                })
                ->delete();
            return back()->with('success', 'Friend removed.');
        }
        
        return back()->with('error', 'No friend relationship found.');
    }
}