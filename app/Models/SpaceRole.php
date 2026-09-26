<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceRole extends Model
{
    protected $fillable = [
        'space_id',
        'name',
        'color',
        'position',
        'permissions',
        'is_default',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_default' => 'boolean',
        'position' => 'integer',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function members()
    {
        return $this->hasMany(SpaceMember::class, 'role_id');
    }

    /**
     * String-value check against the permissions JSON array. Response 2
     * adds a SpacePermission enum and a Space::userHasPermission() method
     * that calls through to this rather than duplicating the array lookup.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
