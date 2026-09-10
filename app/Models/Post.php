<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'content', 'shared_post_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * The original post this one reposted, if any. Nullable — either
     * this post isn't a repost, or the original was deleted (see the
     * nullOnDelete on the migration).
     */
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    public function isLikedBy($userId): bool
    {
        return $this->likes->contains('user_id', $userId);
    }
}