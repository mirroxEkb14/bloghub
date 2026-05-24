<?php

namespace Tests\Unit\Rules;

use App\Rules\LevelUniquePerCreatorProfileRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class LevelUniquePerCreatorProfileRuleTest extends TestCase
{
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    public function test_validate_skips_when_creator_profile_id_missing(): void
    {
        $rule = LevelUniquePerCreatorProfileRule::fromGet(fn () => null);
        $failed = false;
        $rule->validate('level', 1, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_validate_passes_for_unique_level(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();

        $rule = LevelUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            'id' => null,
            default => null,
        });

        $failed = false;
        $rule->validate('level', 99, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
        $this->assertContains($tiers[1]->level, [1, 2, 3]);
    }

    public function test_validate_fails_for_duplicate_level_in_same_profile(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();

        $rule = LevelUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            default => null,
        });

        $failed = false;
        $rule->validate('level', $tiers[2]->level, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed);
    }

    public function test_validate_allows_same_level_on_different_profiles(): void
    {
        ['profile' => $profileB] = $this->createCreatorWithTiers();

        $rule = LevelUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profileB->id,
            default => null,
        });

        $failed = false;
        $rule->validate('level', 99, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_validate_ignores_current_record_when_updating(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();

        $rule = LevelUniquePerCreatorProfileRule::fromGet(fn (string $key) => match ($key) {
            'creator_profile_id' => $profile->id,
            'id' => $tiers[2]->id,
            default => null,
        });

        $failed = false;
        $rule->validate('level', $tiers[2]->level, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed);
    }
}
