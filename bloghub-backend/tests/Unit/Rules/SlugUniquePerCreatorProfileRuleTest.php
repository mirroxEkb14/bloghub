<?php

namespace Tests\Unit\Rules;

use App\Rules\SlugUniquePerCreatorProfileRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\TestCase;

class SlugUniquePerCreatorProfileRuleTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use RefreshDatabase;

    public function test_validate_skips_when_creator_profile_id_missing(): void
    {
        $rule = SlugUniquePerCreatorProfileRule::fromGet(fn () => null);
        $failed = false;
        $rule->validate('slug', 'any-slug', function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_validate_passes_for_unique_slug(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'existing-slug']);

        $rule = SlugUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            'id' => null,
            default => null,
        });

        $failed = false;
        $rule->validate('slug', 'new-unique-slug', function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_validate_fails_for_duplicate_slug_in_same_profile(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $this->createPostForProfile($profile, ['slug' => 'duplicate-slug']);

        $rule = SlugUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            default => null,
        });

        $failed = false;
        $rule->validate('slug', 'duplicate-slug', function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed);
    }

    public function test_validate_allows_same_slug_on_different_profiles(): void
    {
        ['profile' => $profileA] = $this->createCreatorProfile();
        ['profile' => $profileB] = $this->createCreatorProfile();
        $this->createPostForProfile($profileA, ['slug' => 'shared-slug']);

        $rule = SlugUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profileB->id,
            default => null,
        });

        $failed = false;
        $rule->validate('slug', 'shared-slug', function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_validate_ignores_current_record_when_updating(): void
    {
        ['profile' => $profile] = $this->createCreatorProfile();
        $post = $this->createPostForProfile($profile, ['slug' => 'keep-slug']);

        $rule = SlugUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            'id' => $post->id,
            default => null,
        });

        $failed = false;
        $rule->validate('slug', 'keep-slug', function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }
}
