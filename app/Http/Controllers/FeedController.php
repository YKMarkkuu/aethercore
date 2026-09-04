<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function index()
    {
        // Get all friend IDs (sent + received)
        $sentIds = DB::table('friendships')
            ->where('user_id', Auth::id())
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->toArray();

        $receivedIds = DB::table('friendships')
            ->where('friend_id', Auth::id())
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        $friendIds = array_unique(array_merge($sentIds, $receivedIds));
        
        $feedPosts = Post::whereIn('user_id', $friendIds)
                        ->orWhere('user_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->with('user')
                        ->get();
        
        return view('feed', compact('feedPosts'));
    }
}