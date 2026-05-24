<?php

namespace Tests\Feature\Api;

use App\Models\PostLike;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\TestCase;

class CreatorProfilePostApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use RefreshDatabase;

    public function test_index_returns_paginated_posts_for_creator(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $postA = $this->createPostForProfile($profile, [
            'slug' => 'first-post',
            'title' => 'First',
        ]);
        $this->createPostForProfile($profile, [
            'slug' => 'second-post',
            'title' => 'Second',
        ]);

        $res = $this->getJson("/api/creator-profiles/{$profile->slug}/posts");

        $res->assertOk()
            ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'user_has_access']]]);

        $slugs = collect($res->json('data'))->pluck('slug')->all();
        $this->assertContains($postA->slug, $slugs);
        $this->assertContains('second-post', $slugs);
    }

    public function test_index_returns_404_for_unknown_creator(): void
    {
        $this->getJson('/api/creator-profiles/unknown-creator/posts')
            ->assertNotFound();
    }

    public function test_show_returns_public_post_for_guest(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, [
            'slug' => 'guest-visible',
            'title' => 'Guest Post',
            'content_text' => '<p>Visible to all</p>',
        ]);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/guest-visible")
            ->assertOk()
            ->assertJsonPath('data.slug', $post->slug)
            ->assertJsonPath('data.content_text', '<p>Visible to all</p>')
            ->assertJsonPath('data.user_has_access', true);
    }

    public function test_show_returns_404_for_unknown_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/missing-post")
            ->assertNotFound();
    }

    public function test_like_and_view_require_authentication(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'engage-post']);

        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/engage-post/like")
            ->assertUnauthorized();
        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/engage-post/view")
            ->assertUnauthorized();
    }

    public function test_like_creates_like_and_unlike_removes_it(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'like-post']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/like-post/like")
            ->assertNoContent();

        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/like-post")
            ->assertOk()
            ->assertJsonPath('data.user_has_liked', true);

        $this->deleteJson("/api/creator-profiles/{$profile->slug}/posts/like-post/like")
            ->assertNoContent();

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_like_is_idempotent(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'like-twice']);
        $user = User::factory()->create();
        PostLike::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
        Sanctum::actingAs($user);

        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/like-twice/like")
            ->assertNoContent();

        $this->assertSame(1, PostLike::query()->where('post_id', $post->id)->where('user_id', $user->id)->count());
    }

    public function test_record_view_creates_view_once(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'view-post']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/view-post/view")
            ->assertNoContent();
        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/view-post/view")
            ->assertNoContent();

        $this->assertSame(1, PostView::query()->where('post_id', $post->id)->where('user_id', $user->id)->count());

        $this->getJson("/api/creator-profiles/{$profile->slug}/posts/view-post")
            ->assertOk()
            ->assertJsonPath('data.user_has_viewed', true);
    }

    public function test_like_returns_404_for_unknown_post(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/creator-profiles/{$profile->slug}/posts/missing/like")
            ->assertNotFound();
    }
}
