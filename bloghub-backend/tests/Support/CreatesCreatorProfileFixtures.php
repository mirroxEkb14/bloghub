<?php

namespace Tests\Support;

use App\Models\CreatorProfile;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesCreatorProfileFixtures
{
    protected function createCreatorProfile(?User $user = null, array $profileAttributes = []): array
    {
        $user = $user ?? User::factory()->create([
            'username' => 'creator_user_' . fake()->unique()->userName(),
            'is_creator' => true,
        ]);

        $profile = CreatorProfile::query()->create(array_merge([
            'user_id' => $user->id,
            'slug' => 'creator-' . fake()->unique()->slug(2),
            'display_name' => 'Creator ' . fake()->unique()->firstName(),
            'about' => null,
            'profile_avatar_path' => null,
            'profile_cover_path' => null,
            'telegram_url' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'youtube_url' => null,
            'twitch_url' => null,
            'website_url' => null,
        ], $profileAttributes));

        return ['user' => $user, 'profile' => $profile];
    }

    protected function createTag(string $name = 'Gaming'): Tag
    {
        return Tag::query()->create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
        ]);
    }
}
