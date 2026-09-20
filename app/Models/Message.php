<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'user_id', 'content', 'type', 'is_read', 'shared_post_id', 'reply_to_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    /**
     * The message this one is replying to, if any. Nullable — either this
     * isn't a reply, or the original was hard-deleted (normal delete is
     * the is_deleted tombstone, so this is mostly a safety net).
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public static function markConversationAsRead($conversationId, $userId)
    {
        self::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
            ->update(['is_read' => true]);

        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }
}