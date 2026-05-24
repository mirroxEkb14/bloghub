<?php

namespace Tests\Feature\Api;

use App\Enums\SubStatus;
use App\Models\Comment;
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

class PostSocialEngagementApiTest extends TestCase
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

    private function commentsUrl(string $profileSlug, string $postSlug): string
    {
        return "/api/creator-profiles/{$profileSlug}/posts/{$postSlug}/comments";
    }

    private function likeUrl(string $profileSlug, string $postSlug): string
    {
        return "/api/creator-profiles/{$profileSlug}/posts/{$postSlug}/like";
    }

    private function viewUrl(string $profileSlug, string $postSlug): string
    {
        return "/api/creator-profiles/{$profileSlug}/posts/{$postSlug}/view";
    }

    public function test_guest_can_list_comments_on_public_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'public-comments']);
        $author = User::factory()->create();
        Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'content_text' => 'Great read!',
        ]);

        $this->getJson($this->commentsUrl($profile->slug, 'public-comments'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.content_text', 'Great read!')
            ->assertJsonPath('data.0.user.username', $author->username);
    }

    public function test_index_returns_empty_array_when_post_has_no_comments(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'no-comments']);

        $this->getJson($this->commentsUrl($profile->slug, 'no-comments'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_guest_cannot_list_comments_on_tier_locked_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture();

        $this->getJson($this->commentsUrl($profile->slug, $post->slug))
            ->assertStatus(403)
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('required_tier.id', $requiredTier->id);
    }

    public function test_subscriber_can_list_comments_on_tier_locked_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture();
        $user = User::factory()->create();
        $this->subscribeUserToTier($user, $requiredTier);
        Sanctum::actingAs($user);

        $this->getJson($this->commentsUrl($profile->slug, $post->slug))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_store_comment_requires_authentication(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'auth-comment']);

        $this->postJson($this->commentsUrl($profile->slug, 'auth-comment'), [
            'content_text' => 'Hello',
        ])->assertUnauthorized();
    }

    public function test_store_creates_comment_on_public_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'new-comment']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $res = $this->postJson($this->commentsUrl($profile->slug, 'new-comment'), [
            'content_text' => '  My comment  ',
        ]);

        $res->assertOk()
            ->assertJsonPath('data.content_text', 'My comment')
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content_text' => 'My comment',
        ]);

        $this->getJson($this->commentsUrl($profile->slug, 'new-comment'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_validates_comment_content(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'validate-comment']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->commentsUrl($profile->slug, 'validate-comment'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content_text']);

        $this->postJson($this->commentsUrl($profile->slug, 'validate-comment'), [
            'content_text' => '   ',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content_text']);

        $this->postJson($this->commentsUrl($profile->slug, 'validate-comment'), [
            'content_text' => str_repeat('a', 2001),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content_text']);
    }

    public function test_subscriber_can_comment_on_tier_locked_post(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture();
        $user = User::factory()->create();
        $this->subscribeUserToTier($user, $requiredTier);
        Sanctum::actingAs($user);

        $this->postJson($this->commentsUrl($profile->slug, $post->slug), [
            'content_text' => 'Subscriber comment',
        ])
            ->assertOk()
            ->assertJsonPath('data.content_text', 'Subscriber comment');

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content_text' => 'Subscriber comment',
        ]);
    }

    public function test_store_comment_forbidden_on_tier_post_without_subscription(): void
    {
        [, $profile, $requiredTier, $post] = $this->createTierPostFixture();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->commentsUrl($profile->slug, $post->slug), [
            'content_text' => 'Should not work',
        ])
            ->assertStatus(403)
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('required_tier.id', $requiredTier->id);
    }

    public function test_comment_endpoints_return_404_for_unknown_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson($this->commentsUrl($profile->slug, 'missing-post'))
            ->assertNotFound();

        $this->postJson($this->commentsUrl($profile->slug, 'missing-post'), [
            'content_text' => 'Nope',
        ])->assertNotFound();
    }

    public function test_authenticated_user_can_like_post_without_tier_subscription(): void
    {
        [, $profile, , $post] = $this->createTierPostFixture();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson($this->likeUrl($profile->slug, $post->slug))
            ->assertNoContent();

        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_unlike_when_not_liked_still_succeeds(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'unlike-harmless']);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson($this->likeUrl($profile->slug, 'unlike-harmless'))
            ->assertNoContent();
    }

    public function test_view_and_like_return_404_for_unknown_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->viewUrl($profile->slug, 'missing-post'))
            ->assertNotFound();
        $this->postJson($this->likeUrl($profile->slug, 'missing-post'))
            ->assertNotFound();
        $this->deleteJson($this->likeUrl($profile->slug, 'missing-post'))
            ->assertNotFound();
    }

    public function test_record_view_does_not_require_tier_subscription(): void
    {
        [, $profile, , $post] = $this->createTierPostFixture();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson($this->viewUrl($profile->slug, $post->slug))
            ->assertNoContent();

        $this->assertSame(
            1,
            PostView::query()->where('post_id', $post->id)->where('user_id', $user->id)->count()
        );
    }

    private function createTierPostFixture(int $requiredLevel = 2): array
    {
        ['creator' => $creator, 'profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $requiredTier = $tiers[$requiredLevel];
        $post = $this->createPostForProfile($profile, [
            'slug' => 'tier-post-' . fake()->unique()->slug(2),
            'required_tier_id' => $requiredTier->id,
            'title' => 'Tier Locked',
            'content_text' => '<p>Secret</p>',
        ]);

        return [$creator, $profile, $requiredTier, $post];
    }
}
