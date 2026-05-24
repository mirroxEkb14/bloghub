<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\TestCase;

class CreatorProfileFollowApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use RefreshDatabase;

    public function test_follow_and_unfollow_require_authentication(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")->assertUnauthorized();
        $this->deleteJson("/api/creator-profiles/{$profile->slug}/follow")->assertUnauthorized();
    }

    public function test_follow_creates_follow_relationship(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        Sanctum::actingAs($follower);

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")
            ->assertOk();

        $this->assertDatabaseHas('creator_profile_follows', [
            'creator_profile_id' => $profile->id,
            'user_id' => $follower->id,
        ]);
    }

    public function test_follow_is_idempotent(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")
            ->assertOk();

        $this->assertSame(
            1,
            $profile->followers()->where('users.id', $follower->id)->count()
        );
    }

    public function test_follow_forbidden_for_own_profile(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")
            ->assertStatus(422);
    }

    public function test_follow_returns_404_for_unknown_slug(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/creator-profiles/unknown-slug/follow')
            ->assertNotFound();
    }

    public function test_unfollow_removes_follow_relationship(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $this->deleteJson("/api/creator-profiles/{$profile->slug}/follow")
            ->assertOk();

        $this->assertDatabaseMissing('creator_profile_follows', [
            'creator_profile_id' => $profile->id,
            'user_id' => $follower->id,
        ]);
    }

    public function test_show_reflects_follow_state_after_follow_and_unfollow(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        Sanctum::actingAs($follower);

        $this->getJson("/api/creator-profiles/{$profile->slug}")
            ->assertOk()
            ->assertJsonPath('data.is_following', false);

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")->assertOk();

        $this->getJson("/api/creator-profiles/{$profile->slug}")
            ->assertOk()
            ->assertJsonPath('data.is_following', true);

        $this->deleteJson("/api/creator-profiles/{$profile->slug}/follow")->assertOk();

        $this->getJson("/api/creator-profiles/{$profile->slug}")
            ->assertOk()
            ->assertJsonPath('data.is_following', false);
    }

    public function test_unfollow_returns_404_for_unknown_slug(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson('/api/creator-profiles/unknown-slug/follow')
            ->assertNotFound();
    }

    public function test_unfollow_when_not_following_still_succeeds(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/creator-profiles/{$profile->slug}/follow")
            ->assertOk();
    }

    public function test_following_list_requires_authentication(): void
    {
        $this->getJson('/api/me/following')->assertUnauthorized();
    }

    public function test_following_list_empty_when_not_following_anyone(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/me/following')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_following_list_includes_followed_creators(): void
    {
        ['profile' => $profileA] = $this->createCreatorProfile(profileAttributes: [
            'slug' => 'creator-alpha',
            'display_name' => 'Creator Alpha',
        ]);
        ['profile' => $profileB] = $this->createCreatorProfile(profileAttributes: [
            'slug' => 'creator-beta',
            'display_name' => 'Creator Beta',
        ]);
        $this->createCreatorProfile(profileAttributes: [
            'slug' => 'creator-gamma',
            'display_name' => 'Creator Gamma',
        ]);

        $follower = User::factory()->create();
        $profileA->followers()->attach($follower->id);
        $profileB->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $res = $this->getJson('/api/me/following');

        $res->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'creator_profile' => ['id', 'slug', 'display_name'],
                    'followed_at',
                ]],
            ]);

        $slugs = collect($res->json('data'))->pluck('creator_profile.slug')->all();
        $this->assertContains('creator-alpha', $slugs);
        $this->assertContains('creator-beta', $slugs);
        $this->assertNotContains('creator-gamma', $slugs);

        $alpha = collect($res->json('data'))->firstWhere('creator_profile.slug', 'creator-alpha');
        $this->assertNotNull($alpha['followed_at']);
        $this->assertSame('Creator Alpha', $alpha['creator_profile']['display_name']);
    }

    public function test_following_list_removes_creator_after_unfollow(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile(profileAttributes: [
            'slug' => 'unfollow-me',
        ]);
        $follower = User::factory()->create();
        Sanctum::actingAs($follower);

        $this->postJson("/api/creator-profiles/{$profile->slug}/follow")->assertOk();

        $this->getJson('/api/me/following')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.creator_profile.slug', 'unfollow-me');

        $this->deleteJson("/api/creator-profiles/{$profile->slug}/follow")->assertOk();

        $this->getJson('/api/me/following')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
