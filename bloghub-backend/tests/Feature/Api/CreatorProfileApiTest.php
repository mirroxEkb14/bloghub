<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\TestCase;

class CreatorProfileApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use RefreshDatabase;

    public function test_index_returns_paginated_creator_profiles(): void
    {
        ['profile' => $profileA] = $this->createCreatorProfile(profileAttributes: [
            'display_name' => 'Alpha Creator',
        ]);
        $this->createCreatorProfile(profileAttributes: [
            'display_name' => 'Beta Creator',
        ]);

        $res = $this->getJson('/api/creator-profiles');

        $res->assertOk()
            ->assertJsonStructure(['data' => [['id', 'slug', 'display_name', 'posts_count']]]);

        $slugs = collect($res->json('data'))->pluck('slug')->all();
        $this->assertContains($profileA->slug, $slugs);
    }

    public function test_index_filters_by_search_query(): void
    {
        $this->createCreatorProfile(profileAttributes: [
            'display_name' => 'Hidden Creator',
        ]);
        ['profile' => $match] = $this->createCreatorProfile(profileAttributes: [
            'display_name' => 'Unique Searchable Name',
        ]);

        $res = $this->getJson('/api/creator-profiles?search=Unique+Searchable');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_index_filters_by_tag_slug(): void
    {
        $tag = $this->createTag('Science Fiction');
        ['profile' => $tagged] = $this->createCreatorProfile();
        $tagged->tags()->attach($tag->id);
        $this->createCreatorProfile();

        $res = $this->getJson('/api/creator-profiles?tag=' . $tag->slug);

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $tagged->slug);
    }

    public function test_show_returns_profile_by_slug(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile(profileAttributes: [
            'about' => 'About this creator',
        ]);
        Post::query()->create([
            'creator_profile_id' => $profile->id,
            'slug' => 'sample-post',
            'title' => 'Sample',
            'content_text' => '<p>Hi</p>',
            'excerpt' => null,
            'media_url' => null,
            'media_type' => null,
        ]);

        $this->getJson("/api/creator-profiles/{$profile->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $profile->slug)
            ->assertJsonPath('data.display_name', $profile->display_name)
            ->assertJsonPath('data.about', 'About this creator')
            ->assertJsonPath('data.posts_count', 1);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/creator-profiles/does-not-exist')
            ->assertNotFound();
    }

    public function test_show_includes_is_following_for_authenticated_follower(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Sanctum::actingAs($follower);

        $this->getJson("/api/creator-profiles/{$profile->slug}")
            ->assertOk()
            ->assertJsonPath('data.is_following', true);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me/creator-profile')->assertUnauthorized();
    }

    public function test_me_returns_own_profile(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->getJson('/api/me/creator-profile')
            ->assertOk()
            ->assertJsonPath('data.slug', $profile->slug)
            ->assertJsonPath('data.is_following', false);
    }

    public function test_me_returns_404_when_user_has_no_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->getJson('/api/me/creator-profile')
            ->assertNotFound();
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/creator-profiles', [
            'display_name' => 'New Creator',
        ])->assertUnauthorized();
    }

    public function test_store_requires_verified_email(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->postJson('/api/creator-profiles', [
            'display_name' => 'New Creator',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'You must verify your email before creating a creator profile');
    }

    public function test_store_creates_profile_and_sets_is_creator(): void
    {
        $user = User::factory()->create(['is_creator' => false]);
        $tag = $this->createTag();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/creator-profiles', [
            'display_name' => 'Brand New Creator',
            'slug' => 'brand-new-creator',
            'about' => 'Fresh on the platform',
            'tag_ids' => [$tag->id],
        ]);

        $res->assertCreated()
            ->assertJsonPath('slug', 'brand-new-creator')
            ->assertJsonPath('display_name', 'Brand New Creator')
            ->assertJsonPath('about', 'Fresh on the platform')
            ->assertJsonCount(1, 'tags');

        $this->assertDatabaseHas('creator_profiles', [
            'user_id' => $user->id,
            'slug' => 'brand-new-creator',
            'display_name' => 'Brand New Creator',
        ]);
        $this->assertTrue($user->fresh()->is_creator);
        $this->assertDatabaseHas('creator_profile_tag', [
            'creator_profile_id' => $res->json('id'),
            'tag_id' => $tag->id,
        ]);
    }

    public function test_store_generates_slug_from_display_name_when_omitted(): void
    {
        $user = User::factory()->create(['is_creator' => false]);
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/creator-profiles', [
            'display_name' => 'Auto Slug Creator',
        ]);

        $res->assertCreated()
            ->assertJsonPath('slug', 'auto-slug-creator');
    }

    public function test_store_forbidden_when_user_already_has_profile(): void
    {
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->postJson('/api/creator-profiles', [
            'display_name' => 'Second Profile',
        ])->assertForbidden();
    }

    public function test_store_validates_display_name_and_slug(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->postJson('/api/creator-profiles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['display_name']);

        $this->postJson('/api/creator-profiles', [
            'display_name' => 'Bad Slug',
            'slug' => 'Invalid Slug!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_update_me_updates_profile_and_tags(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        $tag = $this->createTag('Art');
        Sanctum::actingAs($user);

        $this->putJson('/api/me/creator-profile', [
            'display_name' => 'Updated Name',
            'about' => 'Updated about',
            'instagram_url' => 'https://www.instagram.com/updated',
            'tag_ids' => [$tag->id],
        ])
            ->assertOk()
            ->assertJsonPath('data.display_name', 'Updated Name')
            ->assertJsonPath('data.about', 'Updated about')
            ->assertJsonPath('data.instagram_url', 'https://www.instagram.com/updated');

        $profile->refresh();
        $this->assertSame('Updated Name', $profile->display_name);
        $this->assertTrue($profile->tags()->where('tags.id', $tag->id)->exists());
    }

    public function test_update_me_forbidden_without_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->putJson('/api/me/creator-profile', [
            'display_name' => 'Nope',
        ])->assertForbidden();
    }

    public function test_update_by_id_requires_profile_owner(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $this->putJson("/api/creator-profiles/{$profile->id}", [
            'display_name' => 'Hijacked',
        ])->assertForbidden();
    }

    public function test_update_by_id_allows_owner_to_change_slug(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->putJson("/api/creator-profiles/{$profile->id}", [
            'slug' => 'renamed-creator',
        ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'renamed-creator');

        $this->assertDatabaseHas('creator_profiles', [
            'id' => $profile->id,
            'slug' => 'renamed-creator',
        ]);
    }

    public function test_destroy_me_deletes_profile_and_clears_is_creator(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->deleteJson('/api/me/creator-profile')
            ->assertOk();

        $this->assertDatabaseMissing('creator_profiles', ['id' => $profile->id]);
        $this->assertFalse($user->fresh()->is_creator);
    }

    public function test_destroy_me_returns_404_without_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->deleteJson('/api/me/creator-profile')
            ->assertNotFound();
    }
}
