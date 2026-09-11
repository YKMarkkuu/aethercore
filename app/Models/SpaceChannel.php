<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceChannel extends Model
{
    protected $fillable = ['space_id', 'name', 'position'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function messages()
    {
        return $this->hasMany(SpaceMessage::class, 'channel_id');
    }
}