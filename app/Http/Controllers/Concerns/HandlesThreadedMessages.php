<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Shared behavior for anything that acts like a "message" — editable,
 * soft-deletable (tombstone), reactable with multiple simultaneous
 * reaction types per user. Used by both DM chat (ConversationController)
 * and Space channel chat (SpaceMessageController).
 *
 * A using controller only needs to implement three small things:
 *  - reactionsTable(): the pivot table name for that message type
 *  - serializeMessage($message): the full JSON shape sent to the client
 *  - userBelongsToThread($message, $userId): authorization check for reacting
 */
trait HandlesThreadedMessages
{
    protected function reactionTypes(): array
    {
        return ['like', 'love', 'laugh', 'wow', 'sad'];
    }

    abstract protected function reactionsTable(): string;

    abstract protected function serializeMessage($message): array;

    abstract protected function userBelongsToThread($message, int $userId): bool;

    /**
     * Reaction counts per type, plus every type the CURRENT user has
     * reacted with on this message ('mine' is an array now — a user can
     * have several active reaction types on one message at once).
     */
    protected function serializeReactions($message): array
    {
        $rows = DB::table($this->reactionsTable())
            ->where('message_id', $message->id)
            ->get();

        $counts = [];
        $mine = [];
        foreach ($rows as $row) {
            $counts[$row->type] = ($counts[$row->type] ?? 0) + 1;
            if ($row->user_id === Auth::id()) {
                $mine[] = $row->type;
            }
        }

        return ['counts' => $counts, 'mine' => $mine];
    }

    protected function updateMessageContent(Request $request, $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        if ($message->is_deleted) {
            abort(422, 'Cannot edit a deleted message.');
        }

        if (isset($message->type) && $message->type === 'shared_post') {
            abort(422, 'Cannot edit a shared post message.');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->content = $request->content;
        $message->edited_at = now();
        $message->save();

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    protected function softDeleteMessage($message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->content = null;
        $message->is_deleted = true;
        $message->save();

        DB::table($this->reactionsTable())->where('message_id', $message->id)->delete();

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    /**
     * Toggle ONE reaction type: on if the user doesn't have it yet on this
     * message, off if they do — fully independent of any other types they
     * already have. Multiple simultaneous types are allowed.
     */
    protected function toggleReaction(Request $request, $message, int $userId)
    {
        if (!$this->userBelongsToThread($message, $userId)) {
            abort(403);
        }

        if ($message->is_deleted) {
            abort(422, 'Cannot react to a deleted message.');
        }

        $request->validate([
            'type' => 'required|string|in:' . implode(',', $this->reactionTypes()),
        ]);

        $table = $this->reactionsTable();
        $type = $request->input('type');

        $existing = DB::table($table)
            ->where('message_id', $message->id)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->delete();
        } else {
            DB::table($table)->insert([
                'message_id' => $message->id,
                'user_id' => $userId,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $message->touch();

        return response()->json(['message' => $this->serializeMessage($message)]);
    }

    /**
     * Shared "after this id, or changed since this timestamp" polling
     * response shape — an edit/delete/reaction all touch() the row, so
     * the client can patch already-rendered messages instead of only
     * ever appending.
     */
    protected function pollingPayload($query, int $afterId): array
    {
        $messages = $query->orderBy('id')->get();

        return [
            'server_time' => now()->toIso8601String(),
            'messages' => $messages->map(function ($message) use ($afterId) {
                return array_merge(
                    $this->serializeMessage($message),
                    ['is_new' => $message->id > $afterId]
                );
            }),
        ];
    }
}