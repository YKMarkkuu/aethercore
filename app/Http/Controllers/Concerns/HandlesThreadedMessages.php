<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Shared behavior for anything that acts like a "message" — editable,
 * soft-deletable (tombstone), reactable. Used by both DM chat
 * (ConversationController → messages/message_reactions) and Space channel
 * chat (SpaceMessageController → space_messages/space_message_reactions).
 *
 * A using controller only needs to implement three small things:
 *  - reactionsTable(): the pivot table name for that message type
 *  - serializeMessage($message): the full JSON shape sent to the client
 *  - userBelongsToThread($message, $userId): authorization check for reacting
 *
 * Everything else (edit, delete, toggle-reaction, and the "new since this
 * id OR changed since this timestamp" polling shape) lives here once, so a
 * future fix or feature (new reaction type, reaction limits, whatever)
 * only has to be written once and both chat surfaces get it.
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
     * Reaction counts + "which one is mine" — both controllers' serializeMessage()
     * call into this so the counting logic itself isn't duplicated either.
     */
    protected function serializeReactions($message): array
    {
        $rows = DB::table($this->reactionsTable())
            ->where('message_id', $message->id)
            ->get();

        $counts = [];
        $mine = null;
        foreach ($rows as $row) {
            $counts[$row->type] = ($counts[$row->type] ?? 0) + 1;
            if ($row->user_id === Auth::id()) {
                $mine = $row->type;
            }
        }

        return ['counts' => $counts, 'mine' => $mine];
    }

    /**
     * Edit a message's content. Author-only. Blocked once deleted, and
     * blocked for a "shared_post"-style message if the model has that
     * concept (DM messages do, Space messages currently don't — the
     * isset() guard makes this a no-op for models without a `type` column).
     */
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

    /**
     * Soft-delete: wipe content, flip the tombstone flag, clear any
     * reactions (a reaction on a tombstone means nothing). Author-only.
     */
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
     * Toggle a reaction: same type again removes it, a different type
     * swaps it, nothing existing inserts a new one. One active reaction
     * per user per message.
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
            ->first();

        if ($existing && $existing->type === $type) {
            DB::table($table)->where('id', $existing->id)->delete();
        } elseif ($existing) {
            DB::table($table)->where('id', $existing->id)->update([
                'type' => $type,
                'updated_at' => now(),
            ]);
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