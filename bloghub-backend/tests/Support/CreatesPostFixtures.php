<?php

namespace Tests\Support;

use App\Models\CreatorProfile;
use App\Models\Post;

trait CreatesPostFixtures
{
    protected function validPostPayload(array $overrides = []): array
    {
        return array_merge([
            'slug' => 'my-post-' . fake()->unique()->slug(2),
            'title' => 'My Post Title',
            'content_text' => '<p>Post body content</p>',
            'excerpt' => 'Short excerpt',
        ], $overrides);
    }

    protected function createPostForProfile(CreatorProfile $profile, array $overrides = []): Post
    {
        return Post::query()->create(array_merge([
            'creator_profile_id' => $profile->id,
            'required_tier_id' => null,
            'slug' => 'post-' . fake()->unique()->slug(2),
            'title' => 'Existing Post',
            'content_text' => '<p>Existing content</p>',
            'excerpt' => null,
            'media_url' => null,
            'media_type' => null,
        ], $overrides));
    }
}
