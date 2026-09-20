<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesThreadedMessages;
use App\Models\SpaceChannel;
use App\Models\SpaceMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceMessageController extends Controller
{
    use HandlesThreadedMessages;

    public function store(Request $request, SpaceChannel $channel)
    {
        if (!$channel->space->isMember(Auth::id())) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'reply_to_id' => 'nullable|integer',
        ]);

        $replyToId = null;
        if ($request->filled('reply_to_id')) {
            // Reply target must be a real, non-deleted message in THIS
            // channel — otherwise silently drop the link (stale client)
            // rather than erroring.
            $validReply = $channel->messages()
                ->where('id', $request->reply_to_id)
                ->where('is_deleted', false)
                ->exists();

            if ($validReply) {
                $replyToId = (int) $request->reply_to_id;
            }
        }

        $message = $channel->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'reply_to_id' => $replyToId,
        ]);

        $message->load(['user', 'replyTo.user']);

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->serializeMessage($message)]);
        }

        return redirect()->back();
    }

    public function latestMessages(Request $request, SpaceChannel $channel)
    {
        if (!$channel->space->isMember(Auth::id())) {
            abort(403);
        }

        $afterId = (int) $request->query('after', 0);
        $since = $request->query('since');

        $query = $channel->messages()->with(['user', 'replyTo.user'])->where('id', '>', $afterId);

        if ($since) {
            $query->orWhere(function ($q) use ($channel, $since, $afterId) {
                $q->where('channel_id', $channel->id)
                    ->where('id', '<=', $afterId)
                    ->where('updated_at', '>', $since);
            });
        }

        return response()->json($this->pollingPayload($query, $afterId));
    }

    public function updateMessage(Request $request, SpaceMessage $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->updateMessageContent($request, $message);
    }

    public function destroyMessage(SpaceMessage $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->softDeleteMessage($message);
    }

    public function reactToMessage(Request $request, SpaceMessage $message)
    {
        $message->load(['user', 'replyTo.user']);
        return $this->toggleReaction($request, $message, Auth::id());
    }

    protected function reactionsTable(): string
    {
        return 'space_message_reactions';
    }

    protected function userBelongsToThread($message, int $userId): bool
    {
        return $message->channel->space->isMember($userId);
    }

    protected function serializeMessage($message): array
    {
        $replyTo = null;
        if ($message->reply_to_id) {
            $original = $message->replyTo;
            if ($original) {
                $replyTo = [
                    'id' => $original->id,
                    'author_name' => $original->user->display_name,
                    'content_excerpt' => $original->is_deleted
                        ? null
                        : \Illuminate\Support\Str::limit(strip_tags($original->content ?? ''), 80),
                    'is_deleted' => (bool) $original->is_deleted,
                ];
            }
            // else: original was hard-deleted — reply_to stays null, client
            // shows "unavailable".
        }

        return [
            'id' => $message->id,
            'content' => $message->is_deleted ? null : $message->content,
            'is_deleted' => (bool) $message->is_deleted,
            'edited_at' => $message->edited_at?->toIso8601String(),
            'reply_to' => $replyTo,
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