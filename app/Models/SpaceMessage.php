<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceMessage extends Model
{
    protected $fillable = ['channel_id', 'user_id', 'content', 'reply_to_id', 'edited_at', 'is_deleted'];

    protected $casts = [
        'is_deleted' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(SpaceChannel::class, 'channel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The message this one is replying to, if any. Nullable — either this
     * isn't a reply, or the original was hard-deleted (the normal delete
     * path is the is_deleted tombstone, so this is mostly a safety net).
     */
    public function replyTo()
    {
        return $this->belongsTo(SpaceMessage::class, 'reply_to_id');
    }
}