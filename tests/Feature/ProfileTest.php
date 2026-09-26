<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()->assertDontSee('Welcome to AetherCore!');
    }

    public function test_profile_bio_can_be_updated_without_a_page_reload(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/update', [
                'bio' => 'A freshly updated bio',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('bio', 'A freshly updated bio');

        $this->assertSame('A freshly updated bio', $user->profile()->value('bio'));
    }

    public function test_top_friends_update_keeps_order_and_returns_current_display_names(): void
    {
        $user = User::factory()->create();
        $firstFriend = User::factory()->create(['name' => 'First Friend', 'username' => 'first_friend']);
        $secondFriend = User::factory()->create(['name' => 'Second Friend', 'username' => 'second_friend']);

        Profile::create(['user_id' => $firstFriend->id, 'display_name' => 'Updated First']);
        Profile::create(['user_id' => $secondFriend->id, 'display_name' => 'Updated Second']);

        foreach ([$firstFriend, $secondFriend] as $friend) {
            DB::table('friendships')->insert([
                'user_id' => $user->id,
                'friend_id' => $friend->id,
                'status' => 'accepted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($user)->postJson(route('profile.top-friends'), [
            'friends' => [$secondFriend->id, $firstFriend->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('friends.0.id', $secondFriend->id)
            ->assertJsonPath('friends.0.display_name', 'Updated Second')
            ->assertJsonPath('friends.1.display_name', 'Updated First');

        $this->assertSame([$secondFriend->id, $firstFriend->id], $user->profile()->value('top_friends'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
