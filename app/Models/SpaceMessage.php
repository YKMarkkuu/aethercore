<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceMessage extends Model
{
    protected $fillable = ['channel_id', 'user_id', 'content'];

    public function channel()
    {
        return $this->belongsTo(SpaceChannel::class, 'channel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}