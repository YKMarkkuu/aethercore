<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsBulkSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_bulk_save_persists_and_returns_saved_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => [
                'visibility' => 'friends',
                'dm_permission' => 'nobody',
                'comment_permission' => 'friends',
                'notify_messages' => false,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('type', 'success')
            ->assertJsonPath('saved.visibility', 'friends')
            ->assertJsonPath('saved.dm_permission', 'nobody')
            ->assertJsonPath('saved.comment_permission', 'friends')
            ->assertJsonPath('saved.notify_messages', false);

        $profile = $user->profile()->first();
        $this->assertSame('friends', $profile->visibility);
        $this->assertSame('nobody', $profile->dm_permission);
        $this->assertSame('friends', $profile->comment_permission);
        $this->assertFalse((bool) $profile->notify_messages);
    }

    public function test_partial_payload_leaves_other_settings_untouched(): void
    {
        $user = User::factory()->create();
        $user->profile()->update([
            'visibility' => 'private',
            'dm_permission' => 'friends',
            'show_status_to' => 'nobody',
            'notify_email' => false,
        ]);

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => ['comment_permission' => 'nobody'],
        ])->assertOk();

        $profile = $user->profile()->first();
        $this->assertSame('nobody', $profile->comment_permission);
        $this->assertSame('private', $profile->visibility);
        $this->assertSame('friends', $profile->dm_permission);
        $this->assertSame('nobody', $profile->show_status_to);
        $this->assertFalse((bool) $profile->notify_email);
        $this->assertTrue((bool) $profile->notify_messages);
    }

    public function test_unknown_key_is_rejected_with_a_422_on_that_key(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => ['nonsense' => 'x'],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['settings.nonsense']);
    }

    public function test_invalid_value_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => ['visibility' => 'invalid'],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['settings.visibility']);

        $this->assertSame('public', $user->profile()->first()->visibility);
    }

    public function test_theme_is_saved_to_the_users_table(): void
    {
        $user = User::factory()->create();
        $this->assertSame('aethercore', $user->fresh()->theme);

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => ['theme' => 'midnight'],
        ])->assertOk()
            ->assertJsonPath('saved.theme', 'midnight');

        $this->assertSame('midnight', $user->fresh()->theme);
    }

    public function test_unknown_theme_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => ['theme' => 'hotdog-stand'],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['settings.theme']);

        $this->assertSame('aethercore', $user->fresh()->theme);
    }

    public function test_missing_profile_row_is_created(): void
    {
        $user = User::factory()->create();
        Profile::where('user_id', $user->id)->delete();
        $this->assertDatabaseMissing('profiles', ['user_id' => $user->id]);

        $this->actingAs($user->fresh())->patchJson(route('settings.preferences'), [
            'settings' => ['dm_permission' => 'friends'],
        ])->assertOk();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'dm_permission' => 'friends',
        ]);
    }

    public function test_one_invalid_key_rejects_the_whole_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => [
                'visibility' => 'friends',
                'dm_permission' => 'bogus',
                'theme' => 'midnight',
            ],
        ])->assertStatus(422);

        $profile = $user->profile()->first();
        $this->assertSame('public', $profile->visibility);
        $this->assertSame('aethercore', $user->fresh()->theme);
    }

    public function test_empty_settings_payload_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson(route('settings.preferences'), [
            'settings' => [],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['settings']);
    }

    public function test_guests_cannot_use_the_endpoint(): void
    {
        $this->patchJson(route('settings.preferences'), [
            'settings' => ['visibility' => 'private'],
        ])->assertUnauthorized();
    }
}
