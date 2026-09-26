<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceBan extends Model
{
    protected $fillable = ['space_id', 'user_id', 'banned_by', 'reason'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
