<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|max:500',
        ]);

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'Post created!');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }

    /**
     * Toggle a like on a post. Anyone can like any post they can see —
     * there's no "friends only" restriction here since the feed itself
     * already only shows friends' + your own posts.
     */
    public function toggleLike(Post $post)
    {
        $userId = Auth::id();

        $existing = PostLike::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PostLike::create(['post_id' => $post->id, 'user_id' => $userId]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $post->likes()->count(),
        ]);
    }

    /**
     * Repost: creates a NEW post row owned by the current user, with an
     * optional caption, pointing at the original via shared_post_id.
     * It gets its own independent likes/comments, same as a Twitter
     * quote-repost would.
     */
    public function repost(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'nullable|string|max:500',
        ]);

        // Reposting a repost points at the ORIGINAL, not the repost
        // itself, so you don't end up with repost-of-repost-of-repost
        // chains — everything traces back to one source post.
        $originalId = $post->shared_post_id ?? $post->id;

        $repost = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content ?? '',
            'shared_post_id' => $originalId,
        ]);

        return response()->json([
            'success' => true,
            'post_id' => $repost->id,
        ]);
    }

    /**
     * Send a post as a message to a friend. Reuses the existing chat
     * infrastructure entirely — this just creates a normal Message with
     * a formatted reference to the post, so it shows up (and broadcasts
     * live) exactly like any other chat message.
     */
    public function shareToChat(Request $request, Post $post)
    {
        $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
        ]);

        $authId = Auth::id();
        $friendId = (int) $request->friend_id;

        $sentIds = DB::table('friendships')
            ->where('user_id', $authId)
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->toArray();

        $receivedIds = DB::table('friendships')
            ->where('friend_id', $authId)
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        $friendIds = array_unique(array_merge($sentIds, $receivedIds));

        if (!in_array($friendId, $friendIds, true)) {
            abort(403, 'You can only share posts with friends.');
        }

        $conversation = Auth::user()->getConversationWith($friendId);

        $excerpt = Str::limit(strip_tags($post->content), 80);
        $profileUrl = route('profile.show', $post->user);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $authId,
            'content' => "Shared {$post->user->display_name}'s post: \"{$excerpt}\" — {$profileUrl}",
        ]);

        $conversation->update(['last_message_at' => now()]);

        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed for shared-post message ' . $message->id . ': ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
        ]);
    }
}