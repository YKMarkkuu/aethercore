<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceMember extends Model
{
    protected $fillable = ['space_id', 'user_id', 'role'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}