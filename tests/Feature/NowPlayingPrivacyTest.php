<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NowPlayingPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.lastfm.api_key', 'test-key');

        Http::fake([
            'https://ws.audioscrobbler.com/2.0/*' => Http::response([
                'recenttracks' => [
                    'track' => [[
                        'name' => 'Secret Song',
                        'artist' => ['#text' => 'Secret Artist'],
                        'album' => ['#text' => 'Secret Album'],
                        'image' => [],
                        '@attr' => ['nowplaying' => 'true'],
                    ]],
                ],
            ]),
        ]);
    }

    /** An owner who is connected and active, so their effective status is 'online'. */
    private function onlineOwner(array $profile): User
    {
        $owner = User::factory()->create([
            'lastfm_username' => 'owner-lastfm',
            'status' => 'online',
            'last_seen_at' => now(),
            'last_active_at' => now(),
        ]);
        $owner->profile()->update($profile);

        return $owner;
    }

    private function befriend(User $a, User $b): void
    {
        DB::table('friendships')->insert([
            'user_id' => $a->id,
            'friend_id' => $b->id,
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_now_playing_returns_offline_for_private_profile(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'private', 'show_status_to' => 'everyone']);
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->getJson(route('now-playing', $owner))
            ->assertOk()
            ->assertJson(['status' => 'offline', 'now_playing' => null]);
    }

    public function test_now_playing_returns_offline_for_friends_only_profile_when_not_friend(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'friends', 'show_status_to' => 'everyone']);
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->getJson(route('now-playing', $owner))
            ->assertOk()
            ->assertJson(['status' => 'offline', 'now_playing' => null]);
    }

    public function test_now_playing_allows_owner_to_see_own_private_profile(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'private', 'show_status_to' => 'everyone']);

        $this->actingAs($owner)
            ->getJson(route('now-playing', $owner))
            ->assertOk()
            ->assertJsonPath('status', 'online')
            ->assertJsonPath('now_playing.name', 'Secret Song');
    }

    public function test_now_playing_still_works_for_friend_on_friends_only_profile(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'friends', 'show_status_to' => 'everyone']);
        $viewer = User::factory()->create();
        $this->befriend($viewer, $owner);

        $this->actingAs($viewer)
            ->getJson(route('now-playing', $owner))
            ->assertOk()
            ->assertJsonPath('status', 'online')
            ->assertJsonPath('now_playing.name', 'Secret Song');
    }

    public function test_batch_returns_offline_for_private_profile_even_for_a_friend(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'private', 'show_status_to' => 'everyone']);
        $viewer = User::factory()->create();
        $this->befriend($viewer, $owner);

        $this->actingAs($viewer)
            ->getJson(route('now-playing.batch', ['ids' => [$owner->id]]))
            ->assertOk()
            ->assertJsonPath("status.{$owner->id}.status", 'offline')
            ->assertJsonPath('now_playing', []);
    }

    public function test_batch_still_shows_friend_on_public_profile(): void
    {
        $owner = $this->onlineOwner(['visibility' => 'public', 'show_status_to' => 'everyone']);
        $viewer = User::factory()->create();
        $this->befriend($viewer, $owner);

        $this->actingAs($viewer)
            ->getJson(route('now-playing.batch', ['ids' => [$owner->id]]))
            ->assertOk()
            ->assertJsonPath("status.{$owner->id}.status", 'online')
            ->assertJsonPath("now_playing.{$owner->id}.name", 'Secret Song');
    }
}
