<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'description', 'icon'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function channels()
    {
        return $this->hasMany(SpaceChannel::class)->orderBy('position');
    }

    public function members()
    {
        return $this->hasMany(SpaceMember::class);
    }

    public function isMember($userId): bool
    {
        return $this->members->contains('user_id', $userId);
    }

    public function isOwner($userId): bool
    {
        return $this->owner_id === $userId;
    }

    public function getIconUrl(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}