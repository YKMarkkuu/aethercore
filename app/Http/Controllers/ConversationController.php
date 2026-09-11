<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * The only reaction types the UI offers — kept as a small fixed set
     * of custom SVG icons rather than a free-form emoji picker, to match
     * the rest of the app's icon language.
     */
    protected const REACTION_TYPES = ['like', 'love', 'laugh', 'wow', 'sad'];

    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['participants.user', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        // Check if user is a participant
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $messages = $conversation->messages()->with(['user', 'sharedPost.user'])->get();

        // Mark messages as read
        Message::markConversationAsRead($conversation->id, Auth::id());

        // Get the other participant for the header
        $otherUser = $conversation->getOtherParticipant(Auth::id());

        // Reaction data for the initial paint, so pills don't "pop in"
        // a few seconds late waiting on the first poll cycle.
        $reactionsByMessage = [];
        if ($messages->isNotEmpty()) {
            $rows = DB::table('message_reactions')
                ->whereIn('message_id', $messages->pluck('id'))
                ->get();

            foreach ($rows as $row) {
                if (!isset($reactionsByMessage[$row->message_id])) {
                    $reactionsByMessage[$row->message_id] = ['counts' => [], 'mine' => null];
                }
                $reactionsByMessage[$row->message_id]['counts'][$row->type] =
                    ($reactionsByMessage[$row->message_id]['counts'][$row->type] ?? 0) + 1;
                if ($row->user_id === Auth::id()) {
                    $reactionsByMessage[$row->message_id]['mine'] = $row->type;
                }
            }
        }

        return view('conversations.show', compact('conversation', 'messages', 'otherUser', 'reactionsByMessage'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('user');

        // Broadcast to whoever else is looking at this conversation right
        // now. Wrapped in try/catch: the message is already safely saved
        // above by this point, so a broadcasting failure should never
        // turn into a failed response for the sender.
        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed for message ' . $message->id . ': ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $this->serializeMessage($message),
            ]);
        }

        return redirect()->back();
    }

    public function startWithUser($userId)
    {
        $conversation = Auth::user()->getConversationWith($userId);
        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Polling endpoint used by conversation-show.blade.php instead of
     * WebSocket broadcasting.
     *
     * Returns two kinds of things in one response:
     *  - brand new messages (id > ?after=)
     *  - EXISTING messages that changed since ?since= (an edit, a
     *    delete, or a reaction — all three bump the message's
     *    updated_at via touch()), so the client can patch them in place
     *    instead of only ever appending.
     *
     * Each message includes an `is_new` flag so the client knows which
     * behavior to apply.
     */
    public function latestMessages(Request $request, Conversation $conversation)
    {
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $afterId = (int) $request->query('after', 0);
        $since = $request->query('since');

        $query = $conversation->messages()->with(['user', 'sharedPost.user'])->where('id', '>', $afterId);

        if ($since) {
            $query->orWhere(function ($q) use ($conversation, $since, $afterId) {
                $q->where('conversation_id', $conversation->id)
                    ->where('id', '<=', $afterId)
                    ->where('updated_at', '>', $since);
            });
        }

        $messages = $query->orderBy('id')->get();

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'messages' => $messages->map(function ($message) use ($afterId) {
                return array_merge(
                    $this->serializeMessage($message),
                    ['is_new' => $message->id > $afterId]
                );
            }),
        ]);
    }

    /**
     * Edit a message's content. Only the original author can do this.
     */
    public function updateMessage(Request $request, Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        if ($message->is_deleted) {
            abort(422, 'Cannot edit a deleted message.');
        }

        if ($message->type === 'shared_post') {
            abort(422, 'Cannot edit a shared post message.');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->content = $request->content;
        $message->edited_at = now();
        $message->save();

        $message->load('user');

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    /**
     * Soft-delete a message: content is wiped and a tombstone is shown
     * in its place, rather than removing the row (so the conversation
     * flow and any reactions history aren't silently rewritten). Only
     * the original author can do this.
     */
    public function destroyMessage(Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->content = null;
        $message->is_deleted = true;
        $message->save();

        // Deleting a message clears any reactions on it too — a reaction
        // on a tombstone doesn't mean anything.
        DB::table('message_reactions')->where('message_id', $message->id)->delete();

        $message->load('user');

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    /**
     * Toggle a reaction on a message. Any participant of the
     * conversation can react to any message in it, including their own.
     * One active reaction per user per message — reacting again with
     * the same type removes it, reacting with a different type swaps it.
     */
    public function reactToMessage(Request $request, Message $message)
    {
        $conversation = $message->conversation;

        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        if ($message->is_deleted) {
            abort(422, 'Cannot react to a deleted message.');
        }

        $request->validate([
            'type' => 'required|string|in:' . implode(',', self::REACTION_TYPES),
        ]);

        $userId = Auth::id();
        $type = $request->input('type');

        $existing = DB::table('message_reactions')
            ->where('message_id', $message->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && $existing->type === $type) {
            // Toggle off
            DB::table('message_reactions')->where('id', $existing->id)->delete();
        } elseif ($existing) {
            // Switch to a different reaction type
            DB::table('message_reactions')->where('id', $existing->id)->update([
                'type' => $type,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('message_reactions')->insert([
                'message_id' => $message->id,
                'user_id' => $userId,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Bump the message's updated_at so polling clients pick up the
        // reaction change even if they've already seen this message.
        $message->touch();
        $message->load('user');

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    /**
     * Consistent JSON shape for a message, used by store(),
     * latestMessages(), updateMessage(), destroyMessage(), and
     * reactToMessage() so the client only needs one code path to render
     * or patch a message regardless of which endpoint it came from.
     */
    protected function serializeMessage(Message $message): array
    {
        $reactionRows = DB::table('message_reactions')
            ->where('message_id', $message->id)
            ->get();

        $counts = [];
        $mine = null;
        foreach ($reactionRows as $row) {
            $counts[$row->type] = ($counts[$row->type] ?? 0) + 1;
            if ($row->user_id === Auth::id()) {
                $mine = $row->type;
            }
        }

        $sharedPost = null;
        if ($message->type === 'shared_post' && $message->shared_post_id) {
            $original = $message->sharedPost;
            if ($original) {
                $original->loadMissing('user');
                $sharedPost = [
                    'id' => $original->id,
                    'content_excerpt' => \Illuminate\Support\Str::limit(strip_tags($original->content ?? ''), 120),
                    'author_name' => $original->user->display_name,
                    'author_avatar' => $original->user->getAvatarUrl(),
                    'profile_url' => route('profile.show', $original->user),
                ];
            }
            // else: original post was deleted — sharedPost stays null,
            // and the client renders a "no longer available" card.
        }

        return [
            'id' => $message->id,
            'type' => $message->type,
            'content' => $message->is_deleted ? null : $message->content,
            'shared_post' => $sharedPost,
            'is_deleted' => (bool) $message->is_deleted,
            'edited_at' => $message->edited_at?->toIso8601String(),
            'time' => $message->created_at->format('g:i A'),
            'created_at' => $message->created_at->toIso8601String(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'display_name' => $message->user->display_name,
                'avatar_url' => $message->user->getAvatarUrl(),
            ],
            'reactions' => [
                'counts' => $counts,
                'mine' => $mine,
            ],
        ];
    }
}