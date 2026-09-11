<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * infrastructure entirely — creates a normal Message, but with
     * type='shared_post' and shared_post_id set, so the chat view
     * renders it as a clickable preview card instead of plain text
     * (see partials in conversation-show.blade.php).
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

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $authId,
            'content' => '',
            'type' => 'shared_post',
            'shared_post_id' => $post->id,
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

    /**
     * Live polling endpoint for the feed and profile Posts panel — given
     * the set of post ids currently rendered on the page, returns fresh
     * like/comment state for each so other people's activity shows up
     * without a refresh. Deliberately simple: no "since" filtering like
     * the chat polling has, since this only ever covers a small, already-
     * bounded set of visible posts, so just refetching current truth for
     * exactly those ids each cycle is simplest and cheap enough.
     */
    public function updates(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array|max:50',
            'post_ids.*' => 'integer',
        ]);

        $userId = Auth::id();

        $posts = Post::whereIn('id', $request->post_ids)
            ->with(['likes', 'comments.user'])
            ->get();

        return response()->json([
            'posts' => $posts->map(function ($post) use ($userId) {
                return [
                    'id' => $post->id,
                    'like_count' => $post->likes->count(),
                    'liked_by_me' => $post->likes->contains('user_id', $userId),
                    'comments' => $post->comments->sortBy('created_at')->values()->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'content' => $c->content,
                            'time' => $c->created_at->diffForHumans(),
                            'user' => [
                                'display_name' => $c->user->display_name,
                                'avatar_url' => $c->user->getAvatarUrl(),
                                'profile_url' => route('profile.show', $c->user),
                            ],
                        ];
                    }),
                ];
            }),
        ]);
    }
}