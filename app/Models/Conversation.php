<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name', 'space_id', 'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // A conversation has many participants
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    // A conversation has many users (through participants)
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants');
    }

    // A conversation has many messages
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    // Get the last message in the conversation
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    // Get the other participant in a direct conversation
    public function getOtherParticipant($userId)
    {
        return $this->users()->where('users.id', '!=', $userId)->first();
    }

    // Find or create a direct conversation between two users
    public static function getOrCreateDirectConversation($user1Id, $user2Id)
    {
        // Check if conversation already exists
        $conversation = self::where('type', 'direct')
            ->whereHas('users', function($q) use ($user1Id) {
                $q->where('user_id', $user1Id);
            })
            ->whereHas('users', function($q) use ($user2Id) {
                $q->where('user_id', $user2Id);
            })
            ->withCount('users')
            ->having('users_count', 2)
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $conversation = self::create([
            'type' => 'direct',
            'last_message_at' => now(),
        ]);

        // Add participants
        $conversation->users()->attach([
            $user1Id => ['role' => 'member'],
            $user2Id => ['role' => 'member'],
        ]);

        return $conversation;
    }
}