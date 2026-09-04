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
    ];

    protected $casts = [
        'top_artists' => 'array',
        'top_songs' => 'array',
        'top_albums' => 'array',
        'top_friends' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}