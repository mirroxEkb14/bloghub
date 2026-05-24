<?php

namespace Tests\Feature\Api;

use App\Enums\MediaType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class CreatorPostApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload())
            ->assertUnauthorized();
    }

    public function test_store_requires_creator_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload())
            ->assertForbidden();
    }

    public function test_store_creates_public_post(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $payload = $this->validPostPayload([
            'slug' => 'new-public-post',
            'title' => 'Hello World',
        ]);

        $res = $this->postJson('/api/me/creator-profile/posts', $payload);

        $res->assertCreated()
            ->assertJsonPath('slug', 'new-public-post')
            ->assertJsonPath('title', 'Hello World')
            ->assertJsonPath('user_has_access', true)
            ->assertJsonPath('required_tier', null);

        $this->assertDatabaseHas('posts', [
            'creator_profile_id' => $profile->id,
            'slug' => 'new-public-post',
            'title' => 'Hello World',
            'required_tier_id' => null,
        ]);
    }

    public function test_store_creates_tier_locked_post(): void
    {
        ['creator' => $user, 'profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload([
            'slug' => 'tier-locked-post',
            'required_tier_id' => $tiers[2]->id,
        ]));

        $res->assertCreated()
            ->assertJsonPath('slug', 'tier-locked-post')
            ->assertJsonPath('required_tier.id', $tiers[2]->id);

        $this->assertDatabaseHas('posts', [
            'slug' => 'tier-locked-post',
            'required_tier_id' => $tiers[2]->id,
        ]);
    }

    public function test_store_validates_required_fields_and_slug_uniqueness(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'duplicate-slug']);
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/posts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug', 'title', 'content_text']);

        $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload([
            'slug' => 'duplicate-slug',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_store_rejects_tier_from_another_creator(): void
    {
        ['tiers' => $otherTiers] = $this->createCreatorWithTiers();
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload([
            'required_tier_id' => $otherTiers[1]->id,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['required_tier_id']);
    }

    public function test_store_accepts_media_fields(): void
    {
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/posts', $this->validPostPayload([
            'slug' => 'media-post',
            'media_url' => 'posts/media/test.png',
            'media_type' => MediaType::Image->value,
        ]))
            ->assertCreated()
            ->assertJsonPath('media_type', MediaType::Image->value)
            ->assertJsonPath('media_url', fn ($url) => str_contains((string) $url, 'posts/media/test.png'));
    }

    public function test_update_changes_post_fields(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'update-me']);
        Sanctum::actingAs($user);

        $this->putJson('/api/me/creator-profile/posts/update-me', [
            'title' => 'Updated Title',
            'content_text' => '<p>Updated body</p>',
            'excerpt' => 'New excerpt',
        ])
            ->assertOk()
            ->assertJsonPath('title', 'Updated Title')
            ->assertJsonPath('excerpt', 'New excerpt');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'excerpt' => 'New excerpt',
        ]);
    }

    public function test_update_forbidden_for_other_creators_post(): void
    {
        ['profile' => $otherProfile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($otherProfile, ['slug' => 'not-mine']);
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->putJson('/api/me/creator-profile/posts/not-mine', [
            'title' => 'Hijacked',
        ])->assertForbidden();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => 'Hijacked',
        ]);
    }

    public function test_destroy_deletes_own_post(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'delete-me']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/me/creator-profile/posts/delete-me')
            ->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_destroy_returns_404_for_unknown_slug(): void
    {
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->deleteJson('/api/me/creator-profile/posts/missing-post')
            ->assertNotFound();
    }
}
