<?php

namespace Tests\Feature\Api;

use App\Enums\SubStatus;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class FeedApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    private function subscribeUserToTier(User $user, Tier $tier): void
    {
        Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
    }

    private function feedPostSlugs(TestResponse $response): array
    {
        return collect($response->json('data'))->pluck('slug')->all();
    }

    public function test_feed_endpoints_require_authentication(): void
    {
        $this->getJson('/api/me/feed')->assertUnauthorized();
        $this->getJson('/api/me/feed/public')->assertUnauthorized();
        $this->getJson('/api/me/feed/tier')->assertUnauthorized();
    }

    public function test_public_feed_empty_without_subscriptions_or_follows(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/me/feed/public')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_public_feed_includes_public_posts_from_subscribed_creator(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $publicPost = $this->createPostForProfile($profile, [
            'slug' => 'subscribed-public',
            'required_tier_id' => null,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'subscribed-tier-only',
            'required_tier_id' => $tiers[2]->id,
        ]);

        $viewer = User::factory()->create();
        $this->subscribeUserToTier($viewer, $tiers[2]);
        Sanctum::actingAs($viewer);

        $slugs = $this->feedPostSlugs($this->getJson('/api/me/feed/public')->assertOk());

        $this->assertSame([$publicPost->slug], $slugs);
    }

    public function test_public_feed_includes_public_posts_from_followed_creator(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, [
            'slug' => 'followed-public',
            'required_tier_id' => null,
        ]);

        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $slugs = $this->feedPostSlugs($this->getJson('/api/me/feed/public')->assertOk());

        $this->assertContains('followed-public', $slugs);
    }

    public function test_public_feed_excludes_unrelated_creators(): void
    {
        ['profile' => $subscribedProfile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        ['profile' => $otherProfile] = $this->createCreatorProfile();
        $this->createPostForProfile($subscribedProfile, [
            'slug' => 'mine-public',
            'required_tier_id' => null,
        ]);
        $this->createPostForProfile($otherProfile, [
            'slug' => 'other-public',
            'required_tier_id' => null,
        ]);

        $viewer = User::factory()->create();
        $this->subscribeUserToTier($viewer, $tiers[1]);
        Sanctum::actingAs($viewer);

        $slugs = $this->feedPostSlugs($this->getJson('/api/me/feed/public')->assertOk());

        $this->assertContains('mine-public', $slugs);
        $this->assertNotContains('other-public', $slugs);
    }

    public function test_tier_feed_includes_posts_within_subscription_level(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $this->createPostForProfile($profile, [
            'slug' => 'tier-one-post',
            'required_tier_id' => $tiers[1]->id,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'tier-two-post',
            'required_tier_id' => $tiers[2]->id,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'tier-three-post',
            'required_tier_id' => $tiers[3]->id,
        ]);

        $viewer = User::factory()->create();
        $this->subscribeUserToTier($viewer, $tiers[2]);
        Sanctum::actingAs($viewer);

        $res = $this->getJson('/api/me/feed/tier')->assertOk();
        $slugs = $this->feedPostSlugs($res);

        $this->assertContains('tier-one-post', $slugs);
        $this->assertContains('tier-two-post', $slugs);
        $this->assertNotContains('tier-three-post', $slugs);

        $postsBySlug = collect($res->json('data'))->keyBy('slug');
        $this->assertTrue($postsBySlug['tier-one-post']['user_has_access']);
        $this->assertTrue($postsBySlug['tier-two-post']['user_has_access']);
    }

    public function test_tier_feed_includes_locked_posts_from_followed_creator_without_subscription(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $this->createPostForProfile($profile, [
            'slug' => 'followed-tier-post',
            'required_tier_id' => $tiers[3]->id,
        ]);

        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $res = $this->getJson('/api/me/feed/tier')->assertOk();
        $slugs = $this->feedPostSlugs($res);

        $this->assertContains('followed-tier-post', $slugs);

        $post = collect($res->json('data'))->firstWhere('slug', 'followed-tier-post');
        $this->assertFalse($post['user_has_access']);
    }

    public function test_tier_feed_empty_without_subscriptions_or_follows(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/me/feed/tier')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_home_feed_combines_public_and_accessible_tier_posts(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $this->createPostForProfile($profile, [
            'slug' => 'home-public',
            'required_tier_id' => null,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'home-tier-two',
            'required_tier_id' => $tiers[2]->id,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'home-tier-three',
            'required_tier_id' => $tiers[3]->id,
        ]);

        $viewer = User::factory()->create();
        $this->subscribeUserToTier($viewer, $tiers[2]);
        Sanctum::actingAs($viewer);

        $res = $this->getJson('/api/me/feed')->assertOk();
        $slugs = $this->feedPostSlugs($res);

        $this->assertContains('home-public', $slugs);
        $this->assertContains('home-tier-two', $slugs);
        $this->assertNotContains('home-tier-three', $slugs);

        $postsBySlug = collect($res->json('data'))->keyBy('slug');
        $this->assertTrue($postsBySlug['home-public']['user_has_access']);
        $this->assertTrue($postsBySlug['home-tier-two']['user_has_access']);
    }

    public function test_home_feed_includes_followed_creator_public_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, [
            'slug' => 'followed-home-public',
            'required_tier_id' => null,
        ]);

        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $slugs = $this->feedPostSlugs($this->getJson('/api/me/feed')->assertOk());

        $this->assertContains('followed-home-public', $slugs);
    }

    public function test_feed_search_filters_by_post_title(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $this->createPostForProfile($profile, [
            'slug' => 'findable-post',
            'title' => 'Unique Feed Keyword',
            'required_tier_id' => null,
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'other-post',
            'title' => 'Something else',
            'required_tier_id' => null,
        ]);

        $viewer = User::factory()->create();
        $this->subscribeUserToTier($viewer, $tiers[1]);
        Sanctum::actingAs($viewer);

        $slugs = $this->feedPostSlugs(
            $this->getJson('/api/me/feed/public?q=Unique+Feed')->assertOk()
        );

        $this->assertSame(['findable-post'], $slugs);
    }
}
