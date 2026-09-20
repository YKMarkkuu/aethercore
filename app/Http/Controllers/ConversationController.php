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

        $messages = $conversation->messages()->with(['user', 'sharedPost.user'])->get();

        Message::markConversationAsRead($conversation->id, Auth::id());

        $otherUser = $conversation->getOtherParticipant(Auth::id());

        // Reaction data for the initial paint, so pills don't "pop in" a
        // few seconds late waiting on the first poll cycle.
        $reactionsByMessage = [];
        if ($messages->isNotEmpty()) {
            $rows = DB::table($this->reactionsTable())
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

        // Wrapped in try/catch: the message is already safely saved above
        // by this point, so a broadcasting failure should never turn into
        // a failed response for the sender.
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

    /**
     * Polling endpoint used by conversation-show.blade.php's ChatThread
     * instance instead of WebSocket broadcasting.
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

        return response()->json($this->pollingPayload($query, $afterId));
    }

    public function updateMessage(Request $request, Message $message)
    {
        $message->load('user');
        return $this->updateMessageContent($request, $message);
    }

    public function destroyMessage(Message $message)
    {
        $message->load('user');
        return $this->softDeleteMessage($message);
    }

    public function reactToMessage(Request $request, Message $message)
    {
        $message->load('user');
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

    /**
     * Consistent JSON shape for a DM message, used by store(), the
     * polling payload, and every trait-driven action so the client only
     * ever needs one render/patch code path.
     */
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
            // else: original post was deleted — sharedPost stays null, and
            // the client renders a "no longer available" card.
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
            'reactions' => $this->serializeReactions($message),
        ];
    }
}