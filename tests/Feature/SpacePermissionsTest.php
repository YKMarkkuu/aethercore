<?php

namespace Tests\Feature;

use App\Enums\SpacePermission;
use App\Models\Space;
use App\Models\SpaceMember;
use App\Models\SpaceRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpacePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_enum_returns_all_permission_values(): void
    {
        $this->assertSame([
            'manage_channels',
            'manage_roles',
            'kick_members',
            'ban_members',
            'delete_messages',
            'mention_everyone',
            'manage_space',
            'manage_messages',
        ], SpacePermission::all());
    }

    public function test_space_owner_always_has_permissions(): void
    {
        $owner = User::factory()->create();
        $space = Space::create([
            'owner_id' => $owner->id,
            'name' => 'Permission Test Space',
        ]);

        $this->assertTrue($space->userHasPermission($owner->id, SpacePermission::MANAGE_ROLES));
    }

    public function test_member_permissions_use_assigned_or_default_role(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $space = Space::create([
            'owner_id' => $owner->id,
            'name' => 'Permission Test Space',
        ]);
        $role = SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Moderator',
            'permissions' => [SpacePermission::KICK_MEMBERS->value],
        ]);
        $defaultRole = SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Member',
            'permissions' => [SpacePermission::MANAGE_MESSAGES->value],
            'is_default' => true,
        ]);
        $membership = SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        DB::table('space_members')->where('id', $membership->id)->update(['role_id' => $role->id]);

        $this->assertTrue($space->userHasPermission($member->id, SpacePermission::KICK_MEMBERS));
        $this->assertFalse($space->userHasPermission($member->id, SpacePermission::BAN_MEMBERS));

        DB::table('space_members')->where('id', $membership->id)->update(['role_id' => null]);
        $space->unsetRelation('members');

        $this->assertSame($defaultRole->id, $space->getUserRole($member->id)?->id);
        $this->assertTrue($space->userHasPermission($member->id, SpacePermission::MANAGE_MESSAGES));
    }

    public function test_space_creation_seeds_default_roles_and_assigns_owner_role(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->postJson(route('spaces.store'), [
            'name' => 'New Space',
            'description' => 'A space with roles',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $space = Space::findOrFail($response->json('space_id'));
        $this->assertSame(['Owner', 'Admin', 'Moderator', 'Member'], $space->roles()->pluck('name')->all());
        $this->assertSame('Owner', $space->getUserRole($owner->id)?->name);
        $this->assertSame('owner', $space->members()->where('user_id', $owner->id)->value('role'));
    }
}
