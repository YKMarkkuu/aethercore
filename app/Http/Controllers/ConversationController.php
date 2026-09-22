<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Http\Controllers\Concerns\HandlesThreadedMessages;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    use HandlesThreadedMessages;

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
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $messages = $conversation->messages()->with(['user', 'sharedPost.user', 'replyTo.user'])->get();

        Message::markConversationAsRead($conversation->id, Auth::id());

        $otherUser = $conversation->getOtherParticipant(Auth::id());

        // Reaction data for the initial paint, so pills don't "pop in" a
        // few seconds late waiting on the first poll cycle. 'mine' is an
        // array now — a user can have several reaction types on one message.
        $reactionsByMessage = [];
        if ($messages->isNotEmpty()) {
            $rows = DB::table($this->reactionsTable())
                ->whereIn('message_id', $messages->pluck('id'))
                ->get();

            foreach ($rows as $row) {
                if (!isset($reactionsByMessage[$row->message_id])) {
                    $reactionsByMessage[$row->message_id] = ['counts' => [], 'mine' => []];
                }
                $reactionsByMessage[$row->message_id]['counts'][$row->type] =
                    ($reactionsByMessage[$row->message_id]['counts'][$row->type] ?? 0) + 1;
                if ($row->user_id === Auth::id()) {
                    $reactionsByMessage[$row->message_id]['mine'][] = $row->type;
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

        $other = $conversation->getOtherParticipant(Auth::id());
        if ($other && (Auth::user()->isBlocking($other->id) || Auth::user()->isBlockedBy($other->id))) {
            abort(403, 'You cannot message this user.');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'reply_to_id' => 'nullable|integer',
        ]);

        $replyToId = null;
        if ($request->filled('reply_to_id')) {
            // Reply target must be a real, non-deleted message in THIS
            // conversation — otherwise silently drop the link (stale
            // client) rather than erroring.
            $validReply = $conversation->messages()
                ->where('id', $request->reply_to_id)
                ->where('is_deleted', false)
                ->exists();

            if ($validReply) {
                $replyToId = (int) $request->reply_to_id;
            }
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'reply_to_id' => $replyToId,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load(['user', 'replyTo.user']);

        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed for message ' . $message->id . ': ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->serializeMessage($message)]);
        }

        return redirect()->back();
    }

    public function startWithUser($userId)
    {
        $conversation = Auth::user()->getConversationWith($userId);
        return redirect()->route('conversations.show', $conversation);
    }

    public function latestMessages(Request $request, Conversation $conversation)
    {
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $afterId = (int) $request->query('after', 0);
        $since = $request->query('since');

        $query = $conversation->messages()->with(['user', 'sharedPost.user', 'replyTo.user'])->where('id', '>', $afterId);

        if ($since) {
            $query->orWhere(function ($q) use ($conversation, $since, $afterId) {
                $q->where('conversation_id', $conversation->id)
                    ->where('id', '<=', $afterId)
                    ->where('updated_at', '>', $since);
            });
        }

        return response()->json($this->pollingPayload($query, $afterId));
    }

    public function updateMessage(Request $request, Message $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->updateMessageContent($request, $message);
    }

    public function destroyMessage(Message $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->softDeleteMessage($message);
    }

    public function reactToMessage(Request $request, Message $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->toggleReaction($request, $message, Auth::id());
    }

    protected function reactionsTable(): string
    {
        return 'message_reactions';
    }

    protected function userBelongsToThread($message, int $userId): bool
    {
        return $message->conversation->users()->where('user_id', $userId)->exists();
    }

    protected function serializeMessage($message): array
    {
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
        }

        $replyTo = null;
        if ($message->reply_to_id) {
            $original = $message->replyTo;
            if ($original) {
                $excerpt = $original->is_deleted
                    ? null
                    : ($original->type === 'shared_post'
                        ? 'Shared a post'
                        : \Illuminate\Support\Str::limit(strip_tags($original->content ?? ''), 80));

                $replyTo = [
                    'id' => $original->id,
                    'author_name' => $original->user->display_name,
                    'content_excerpt' => $excerpt,
                    'is_deleted' => (bool) $original->is_deleted,
                ];
            }
        }

        return [
            'id' => $message->id,
            'type' => $message->type,
            'content' => $message->is_deleted ? null : $message->content,
            'shared_post' => $sharedPost,
            'reply_to' => $replyTo,
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
            'reactions' => $this->serializeReactions($message),
        ];
    }
}