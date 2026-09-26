<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'display_name',
        'avatar',
        'banner',
        'bio',
        'location',
        'top_artists',
        'top_songs',
        'top_albums',
        'top_friends',
        'is_public',
        'visibility',
        'dm_permission',
        'show_status_to',
        'notify_email',
        'notify_friend_requests',
        'notify_messages',
        'notify_likes_comments',
    ];

    protected $casts = [
        'top_artists' => 'array',
        'top_songs' => 'array',
        'top_albums' => 'array',
        'top_friends' => 'array',
        'is_public' => 'boolean',
        'notify_email' => 'boolean',
        'notify_friend_requests' => 'boolean',
        'notify_messages' => 'boolean',
        'notify_likes_comments' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}