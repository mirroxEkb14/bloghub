<?php

namespace Tests\Feature\Api;

use App\Enums\SubStatus;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TierPostAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createTierPostFixture(int $requiredLevel = 2): array
    {
        $creator = User::factory()->create([
            'username' => 'creator_user_' . fake()->unique()->userName(),
            'is_creator' => true,
        ]);

        $profile = CreatorProfile::query()->create([
            'user_id' => $creator->id,
            'slug' => 'creator-' . fake()->unique()->slug(2),
            'display_name' => 'Creator ' . fake()->unique()->firstName(),
            'about' => null,
            'profile_avatar_path' => null,
            'profile_cover_path' => null,
        ]);

        $tiers = [];
        for ($lvl = 1; $lvl <= max(3, $requiredLevel); $lvl++) {
            $tiers[$lvl] = Tier::query()->create([
                'creator_profile_id' => $profile->id,
                'level' => $lvl,
                'tier_name' => "Tier $lvl",
                'tier_desc' => "Tier $lvl description",
                'price' => 100 * $lvl,
                'tier_currency' => 'USD',
            ]);
        }

        $requiredTier = $tiers[$requiredLevel];

        $post = Post::query()->create([
            'creator_profile_id' => $profile->id,
            'required_tier_id' => $requiredTier->id,
            'slug' => 'locked-post-' . fake()->unique()->slug(2),
            'title' => 'Locked Post',
            'content_text' => '<p>Secret content</p>',
            'excerpt' => null,
            'media_url' => null,
            'media_type' => null,
        ]);

        return [$creator, $profile, $requiredTier, $post];
    }

    private function subscribeUserToTier(User $user,Tier $tier, ?Carbon $endDate = null, SubStatus $status = SubStatus::Active): Subscription
    {
        return Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'start_date' => now()->subDay(),
            'end_date' => $endDate ?? now()->addDays(30),
            'sub_status' => $status,
        ]);
    }

    public function test_guest_cannot_access_tier_post_by_direct_url(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture(2);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/{$post->slug}")
            ->assertStatus(403)
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('required_tier.id', $requiredTier->id)
            ->assertJsonPath('required_tier.tier_name', $requiredTier->tier_name);
    }

    public function test_authenticated_user_without_subscription_cannot_access_tier_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture(2);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/{$post->slug}")
            ->assertStatus(403)
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('required_tier.id', $requiredTier->id);
    }

    public function test_authenticated_user_with_required_tier_can_access_tier_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture(2);
        $user = User::factory()->create();
        $this->subscribeUserToTier($user, $requiredTier);
        Sanctum::actingAs($user);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/{$post->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.slug', $post->slug)
            ->assertJsonPath('data.content_text', $post->content_text);
    }

    public function test_authenticated_user_with_higher_tier_can_access_tier_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture(2);
        $higherTier = Tier::query()
            ->where('creator_profile_id', $profile->id)
            ->where('level', '>', $requiredTier->level)
            ->orderByDesc('level')
            ->firstOrFail();

        $user = User::factory()->create();
        $this->subscribeUserToTier($user, $higherTier);
        Sanctum::actingAs($user);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/{$post->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);
    }

    public function test_creator_profile_owner_can_access_own_tier_post_without_subscription(): void
    {
        [$creator, $profile, , $post] = $this->createTierPostFixture(2);
        Sanctum::actingAs($creator);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/{$post->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);
    }
}

