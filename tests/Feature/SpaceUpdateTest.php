<?php

namespace Tests\Feature;

use App\Enums\SpacePermission;
use App\Models\Space;
use App\Models\SpaceMember;
use App\Models\SpaceRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpaceUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function makeSpace(User $owner, array $attributes = []): Space
    {
        return Space::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'Original Name',
            'description' => 'Original description',
        ], $attributes));
    }

    public function test_owner_can_update_name(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $response = $this->actingAs($owner)->putJson(route('spaces.update', $space), [
            'name' => 'Renamed Space',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('space.name', 'Renamed Space');

        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Renamed Space']);
    }

    public function test_owner_can_update_description(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $response = $this->actingAs($owner)->putJson(route('spaces.update', $space), [
            'description' => 'A brand new description',
        ]);

        $response->assertOk()
            ->assertJsonPath('space.description', 'A brand new description');

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'description' => 'A brand new description',
        ]);
    }

    public function test_owner_can_update_multiple_fields(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $this->actingAs($owner)->putJson(route('spaces.update', $space), [
            'name' => 'New Name',
            'description' => 'New description',
        ])->assertOk();

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'name' => 'New Name',
            'description' => 'New description',
        ]);
    }

    public function test_owner_can_upload_new_icon(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $response = $this->actingAs($owner)->put(
            route('spaces.update', $space),
            ['icon' => UploadedFile::fake()->image('icon.png')],
            ['Accept' => 'application/json']
        );

        $response->assertOk()->assertJsonPath('success', true);

        $icon = $space->fresh()->icon;
        $this->assertNotNull($icon);
        $this->assertStringStartsWith('space-icons/', $icon);
        Storage::disk('public')->assertExists($icon);
    }

    public function test_partial_update_leaves_other_fields_untouched(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner, ['description' => 'Keep me']);

        $this->actingAs($owner)->putJson(route('spaces.update', $space), [
            'name' => 'Only The Name Changed',
        ])->assertOk();

        $space->refresh();
        $this->assertSame('Only The Name Changed', $space->name);
        $this->assertSame('Keep me', $space->description);
    }

    public function test_non_owner_with_manage_space_permission_can_update(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $space = $this->makeSpace($owner);

        $role = SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Space Manager',
            'permissions' => [SpacePermission::MANAGE_SPACE->value],
        ]);
        SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => $member->id,
            'role' => 'member',
            'role_id' => $role->id,
        ]);

        $this->actingAs($member)->putJson(route('spaces.update', $space), [
            'name' => 'Edited By Manager',
        ])->assertOk();

        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Edited By Manager']);
    }

    public function test_non_owner_without_permission_cannot_update(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $space = $this->makeSpace($owner);

        $defaultRole = SpaceRole::create([
            'space_id' => $space->id,
            'name' => 'Member',
            'permissions' => [],
            'is_default' => true,
        ]);
        SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => $member->id,
            'role' => 'member',
            'role_id' => $defaultRole->id,
        ]);

        $this->actingAs($member)->putJson(route('spaces.update', $space), [
            'name' => 'Hijacked',
        ])->assertForbidden();

        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Original Name']);
    }

    public function test_guest_cannot_update(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $this->putJson(route('spaces.update', $space), [
            'name' => 'Nope',
        ])->assertUnauthorized();

        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Original Name']);
    }

    public function test_svg_upload_is_rejected(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $response = $this->actingAs($owner)->put(
            route('spaces.update', $space),
            ['icon' => UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml')],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['icon']);
        $this->assertNull($space->fresh()->icon);
    }

    public function test_name_too_long_is_rejected(): void
    {
        $owner = User::factory()->create();
        $space = $this->makeSpace($owner);

        $this->actingAs($owner)->putJson(route('spaces.update', $space), [
            'name' => str_repeat('a', 101),
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Original Name']);
    }
}
