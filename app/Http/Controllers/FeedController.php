<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index()
    {
        $friendIds = Auth::user()->friends()->pluck('friend_id');
        $feedPosts = Post::whereIn('user_id', $friendIds)
                        ->orWhere('user_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->with('user')
                        ->get();
        
        return view('feed', compact('feedPosts'));
    }
}