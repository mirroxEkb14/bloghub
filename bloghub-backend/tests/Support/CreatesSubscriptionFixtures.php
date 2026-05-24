<?php

namespace Tests\Support;

use App\Models\CreatorProfile;
use App\Models\Tier;
use App\Models\User;

trait CreatesSubscriptionFixtures
{
    protected function createCreatorWithTiers(array $tierOverrides = []): array
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

        $defaults = [
            1 => [
                'tier_name' => 'Tier 1',
                'tier_desc' => 'Free tier',
                'price' => 0,
                'tier_currency' => 'USD',
            ],
            2 => [
                'tier_name' => 'Tier 2',
                'tier_desc' => 'Standard tier',
                'price' => 10,
                'tier_currency' => 'USD',
            ],
            3 => [
                'tier_name' => 'Tier 3',
                'tier_desc' => 'Premium tier',
                'price' => 20,
                'tier_currency' => 'USD',
            ],
        ];

        $tiers = [];
        foreach ($defaults as $level => $default) {
            $tiers[$level] = Tier::query()->create(array_merge([
                'creator_profile_id' => $profile->id,
                'level' => $level,
            ], $default, $tierOverrides[$level] ?? []));
        }

        return [
            'creator' => $creator,
            'profile' => $profile,
            'tiers' => $tiers,
        ];
    }
}
