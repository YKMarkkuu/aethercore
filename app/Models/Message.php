<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'user_id', 'content', 'type', 'is_read', 'shared_post_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The post this message references, when type === 'shared_post'.
     * Nullable — either this isn't a shared-post message, or the
     * original post was deleted (see nullOnDelete on the migration).
     */
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public static function markConversationAsRead($conversationId, $userId)
    {
        // Mark all messages in the conversation as read
        self::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
            ->update(['is_read' => true]);

        // Update the participant's last_read_at
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }
}