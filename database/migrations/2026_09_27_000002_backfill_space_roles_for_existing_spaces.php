<?php

use App\Models\Space;
use App\Models\SpaceMember;
use App\Models\SpaceRole;
use App\Services\SpaceRoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Space::whereDoesntHave('roles')->each(function (Space $space) {
            $ownerRole = SpaceRoleSeeder::seedDefaultRoles($space);

            SpaceMember::where('space_id', $space->id)
                ->where('role', 'owner')
                ->update(['role_id' => $ownerRole->id]);
        });
    }

    public function down(): void
    {
        //
    }
};
