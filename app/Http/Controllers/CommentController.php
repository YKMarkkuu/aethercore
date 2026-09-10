<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'time' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'display_name' => $comment->user->display_name,
                    'avatar_url' => $comment->user->getAvatarUrl(),
                    'profile_url' => route('profile.show', $comment->user),
                ],
            ],
        ]);
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }
}