<?php

namespace Tests\Feature\Api;

use App\Enums\SubStatus;
use App\Models\PostView;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class ExploreApiTest extends TestCase
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

    public function test_popular_creators_returns_expected_shape(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $this->subscribeUserToTier(User::factory()->create(), $tiers[1]);

        $res = $this->getJson('/api/explore/popular-creators');

        $res->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'slug',
                    'display_name',
                    'posts_count',
                    'subscriptions_count',
                    'user' => ['id', 'name', 'username'],
                    'tags',
                ]],
            ])
            ->assertJsonPath('data.0.slug', $profile->slug)
            ->assertJsonPath('data.0.subscriptions_count', 1);
    }

    public function test_popular_creators_orders_by_active_subscriber_count(): void
    {
        ['profile' => $topProfile, 'tiers' => $topTiers] = $this->createCreatorWithTiers();
        ['profile' => $secondProfile, 'tiers' => $secondTiers] = $this->createCreatorWithTiers();

        foreach (range(1, 3) as $_) {
            $this->subscribeUserToTier(User::factory()->create(), $topTiers[1]);
        }
        $this->subscribeUserToTier(User::factory()->create(), $secondTiers[1]);

        $slugs = collect($this->getJson('/api/explore/popular-creators')->json('data'))
            ->pluck('slug')
            ->all();

        $this->assertSame([$topProfile->slug, $secondProfile->slug], array_slice($slugs, 0, 2));
    }

    public function test_popular_creators_excludes_creators_without_active_subscribers(): void
    {
        ['profile' => $subscribedProfile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        ['profile' => $unsubscribedProfile] = $this->createCreatorProfile();
        $this->subscribeUserToTier(User::factory()->create(), $tiers[1]);

        $slugs = collect($this->getJson('/api/explore/popular-creators')->json('data'))
            ->pluck('slug')
            ->all();

        $this->assertContains($subscribedProfile->slug, $slugs);
        $this->assertNotContains($unsubscribedProfile->slug, $slugs);
    }

    public function test_popular_creators_is_public(): void
    {
        $this->getJson('/api/explore/popular-creators')->assertOk();
    }

    public function test_trending_posts_returns_expected_shape_for_guest(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'trending-shape']);
        $viewer = User::factory()->create();
        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => $viewer->id,
            'created_at' => now(),
        ]);

        $res = $this->getJson('/api/explore/trending-posts');

        $res->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'slug',
                    'title',
                    'content_text',
                    'views_count',
                    'comments_count',
                    'likes_count',
                    'user_has_access',
                    'creator_profile' => ['slug', 'display_name'],
                ]],
            ])
            ->assertJsonPath('data.0.slug', 'trending-shape')
            ->assertJsonPath('data.0.views_count', 1)
            ->assertJsonPath('data.0.user_has_access', true);
    }

    public function test_trending_posts_orders_by_recent_view_count(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $hotPost = $this->createPostForProfile($profile, ['slug' => 'hot-post']);
        $coolPost = $this->createPostForProfile($profile, ['slug' => 'cool-post']);
        $viewer = User::factory()->create();

        foreach (range(1, 3) as $_) {
            PostView::query()->create([
                'post_id' => $hotPost->id,
                'user_id' => User::factory()->create()->id,
                'created_at' => now()->subDays(2),
            ]);
        }
        PostView::query()->create([
            'post_id' => $coolPost->id,
            'user_id' => $viewer->id,
            'created_at' => now()->subDays(2),
        ]);

        $slugs = collect($this->getJson('/api/explore/trending-posts')->json('data'))
            ->pluck('slug')
            ->all();

        $this->assertSame('hot-post', $slugs[0]);
        $this->assertContains('cool-post', $slugs);
    }

    public function test_trending_posts_counts_only_views_from_last_thirty_days(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'old-views-only']);
        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'created_at' => now()->subDays(31),
        ]);
        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'created_at' => now()->subDays(5),
        ]);

        $item = collect($this->getJson('/api/explore/trending-posts')->json('data'))
            ->firstWhere('slug', 'old-views-only');

        $this->assertNotNull($item);
        $this->assertSame(1, $item['views_count']);
    }

    public function test_trending_posts_guest_has_no_access_to_tier_locked_posts(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $post = $this->createPostForProfile($profile, [
            'slug' => 'tier-trending',
            'required_tier_id' => $tiers[2]->id,
        ]);
        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'created_at' => now(),
        ]);

        $item = collect($this->getJson('/api/explore/trending-posts')->json('data'))
            ->firstWhere('slug', 'tier-trending');

        $this->assertNotNull($item);
        $this->assertFalse($item['user_has_access']);
        $this->assertNotNull($item['required_tier']);
    }

    public function test_trending_posts_subscriber_has_access_to_tier_locked_posts(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $post = $this->createPostForProfile($profile, [
            'slug' => 'tier-trending-sub',
            'required_tier_id' => $tiers[2]->id,
        ]);
        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'created_at' => now(),
        ]);

        $subscriber = User::factory()->create();
        $this->subscribeUserToTier($subscriber, $tiers[2]);
        Sanctum::actingAs($subscriber);

        $item = collect($this->getJson('/api/explore/trending-posts')->json('data'))
            ->firstWhere('slug', 'tier-trending-sub');

        $this->assertNotNull($item);
        $this->assertTrue($item['user_has_access']);
        $this->assertArrayHasKey('user_has_liked', $item);
    }

    public function test_trending_posts_is_available_with_optional_authentication(): void
    {
        $this->getJson('/api/explore/trending-posts')->assertOk();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/explore/trending-posts')->assertOk();
    }
}
