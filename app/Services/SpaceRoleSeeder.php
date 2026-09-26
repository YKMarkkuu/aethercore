<?php

namespace App\Services;

use App\Enums\SpacePermission;
use App\Models\Space;
use App\Models\SpaceRole;

/**
 * Seeds the 4 default roles for a newly created Space. Not a Laravel
 * db:seed seeder (those run once, globally) — this runs once PER SPACE,
 * at creation time, called directly from SpaceController::store().
 */
class SpaceRoleSeeder
{
    /**
     * Creates Owner/Admin/Moderator/Member and returns the Owner role,
     * so the caller can immediately assign it to the space's creator.
     */
    public static function seedDefaultRoles(Space $space): SpaceRole
    {
        $owner = SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Owner',
            'color' => '#f0a030',
            'position' => 100,
            'permissions' => SpacePermission::all(),
            'is_default' => false,
        ]);

        SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Admin',
            'color' => '#c04040',
            'position' => 80,
            'permissions' => [
                SpacePermission::MANAGE_CHANNELS->value,
                SpacePermission::MANAGE_ROLES->value,
                SpacePermission::KICK_MEMBERS->value,
                SpacePermission::BAN_MEMBERS->value,
                SpacePermission::DELETE_MESSAGES->value,
                SpacePermission::MENTION_EVERYONE->value,
                SpacePermission::MANAGE_MESSAGES->value,
                SpacePermission::MANAGE_SPACE->value,
            ],
            'is_default' => false,
        ]);

        SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Moderator',
            'color' => '#3a7bd5',
            'position' => 60,
            'permissions' => [
                SpacePermission::KICK_MEMBERS->value,
                SpacePermission::DELETE_MESSAGES->value,
                SpacePermission::MANAGE_MESSAGES->value,
            ],
            'is_default' => false,
        ]);

        SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Member',
            'color' => '#99aab5',
            'position' => 0,
            'permissions' => [],
            'is_default' => true,
        ]);

        return $owner;
    }
}
