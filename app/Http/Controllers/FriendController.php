<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
        // Show friends page with search
        public function index(Request $request)
    {
        $search = $request->get('search');
        
        // Get friends
        $friends = Auth::user()->friends()->get();
        
        // Get friend requests
        $requests = Auth::user()->friendRequests()->get();
        
        // Get users (search by username ONLY)
        $users = User::where('id', '!=', Auth::id());
        
        if ($search) {
            $users->where('name', 'LIKE', '%' . $search . '%');
        }
        
        $users = $users->get();
        
        return view('friends', compact('friends', 'requests', 'users'));
    }

    // Send friend request
    public function sendRequest($userId)
    {
        $friend = User::findOrFail($userId);
        
        if (Auth::user()->id === $friend->id) {
            return back()->with('error', 'You cannot befriend yourself.');
        }
        
        // Check if already friends
        if (Auth::user()->isFriendWith($friend->id)) {
            return back()->with('error', 'You are already friends.');
        }
        
        // Check if request already sent
        $existing = Auth::user()->friends()->where('friend_id', $friend->id)->wherePivot('status', 'pending')->exists();
        if ($existing) {
            return back()->with('error', 'Friend request already sent.');
        }
        
        Auth::user()->friends()->attach($friend->id, ['status' => 'pending']);
        
        return back()->with('success', 'Friend request sent!');
    }

    // Accept friend request
    public function acceptRequest($userId)
    {
        $friend = User::findOrFail($userId);
        
        Auth::user()->friends()->updateExistingPivot($friend->id, ['status' => 'accepted']);
        
        return back()->with('success', 'Friend request accepted!');
    }

    // Reject or unfriend
    public function rejectRequest($userId)
    {
        $friend = User::findOrFail($userId);
        Auth::user()->friends()->detach($friend->id);
        
        return back()->with('success', 'Friend removed.');
    }
}