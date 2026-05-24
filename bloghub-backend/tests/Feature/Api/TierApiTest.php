<?php

namespace Tests\Feature\Api;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class TierApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    private function validTierPayload(array $overrides = []): array
    {
        return array_merge([
            'tier_name' => 'Supporter',
            'tier_desc' => 'Access to exclusive posts',
            'price' => 5,
            'tier_currency' => 'USD',
        ], $overrides);
    }

    public function test_index_returns_tiers_ordered_by_level(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();

        $res = $this->getJson("/api/creator-profiles/{$profile->slug}/tiers");

        $res->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.level', 1)
            ->assertJsonPath('data.0.id', $tiers[1]->id)
            ->assertJsonPath('data.1.level', 2)
            ->assertJsonPath('data.2.level', 3);
    }

    public function test_index_returns_404_for_unknown_creator(): void
    {
        $this->getJson('/api/creator-profiles/unknown-creator/tiers')
            ->assertNotFound();
    }

    public function test_index_my_requires_authentication(): void
    {
        $this->getJson('/api/me/creator-profile/tiers')->assertUnauthorized();
    }

    public function test_index_my_returns_404_without_creator_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->getJson('/api/me/creator-profile/tiers')
            ->assertNotFound();
    }

    public function test_index_my_returns_own_tiers(): void
    {
        ['creator' => $user, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        Sanctum::actingAs($user);

        $this->getJson('/api/me/creator-profile/tiers')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $tiers[1]->id);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/me/creator-profile/tiers', $this->validTierPayload())
            ->assertUnauthorized();
    }

    public function test_store_requires_creator_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->postJson('/api/me/creator-profile/tiers', $this->validTierPayload())
            ->assertForbidden();
    }

    public function test_store_creates_first_tier_at_level_one(): void
    {
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/me/creator-profile/tiers', $this->validTierPayload([
            'tier_name' => 'Entry Tier',
            'price' => 0,
        ]));

        $res->assertCreated()
            ->assertJsonPath('level', 1)
            ->assertJsonPath('tier_name', 'Entry Tier')
            ->assertJsonPath('price', 0)
            ->assertJsonPath('tier_currency', 'USD');

        $this->assertDatabaseHas('tiers', [
            'tier_name' => 'Entry Tier',
            'level' => 1,
            'price' => 0,
        ]);
    }

    public function test_store_assigns_next_available_level(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        Tier::query()->create([
            'creator_profile_id' => $profile->id,
            'level' => 1,
            'tier_name' => 'Tier 1',
            'tier_desc' => 'First',
            'price' => 0,
            'tier_currency' => 'USD',
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/tiers', $this->validTierPayload([
            'tier_name' => 'Tier 2',
            'price' => 10,
        ]))
            ->assertCreated()
            ->assertJsonPath('level', 2)
            ->assertJsonPath('tier_name', 'Tier 2');
    }

    public function test_store_validates_required_fields(): void
    {
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/tiers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tier_name', 'tier_desc', 'price', 'tier_currency']);
    }

    public function test_store_rejects_when_max_tiers_reached(): void
    {
        ['creator' => $user] = $this->createCreatorWithTiers();
        Sanctum::actingAs($user);

        $this->postJson('/api/me/creator-profile/tiers', $this->validTierPayload([
            'tier_name' => 'Extra Tier',
        ]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'You can have at most 3 tiers');
    }

    public function test_update_changes_tier_fields(): void
    {
        ['creator' => $user, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        Sanctum::actingAs($user);

        $this->putJson("/api/me/creator-profile/tiers/{$tiers[2]->id}", [
            'tier_name' => 'Updated Tier',
            'tier_desc' => 'Updated description',
            'price' => 15,
            'tier_currency' => 'EUR',
        ])
            ->assertOk()
            ->assertJsonPath('data.tier_name', 'Updated Tier')
            ->assertJsonPath('data.tier_desc', 'Updated description')
            ->assertJsonPath('data.price', 15)
            ->assertJsonPath('data.tier_currency', 'EUR');

        $this->assertDatabaseHas('tiers', [
            'id' => $tiers[2]->id,
            'tier_name' => 'Updated Tier',
            'price' => 15,
            'tier_currency' => 'EUR',
        ]);
    }

    public function test_update_forbidden_for_other_creators_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/me/creator-profile/tiers/{$tiers[2]->id}", [
            'tier_name' => 'Hijacked',
        ])->assertForbidden();
    }

    public function test_destroy_deletes_own_tier(): void
    {
        ['user' => $user, 'profile' => $profile] = $this->createCreatorProfile();
        $tier = Tier::query()->create([
            'creator_profile_id' => $profile->id,
            'level' => 1,
            'tier_name' => 'Removable',
            'tier_desc' => 'To delete',
            'price' => 5,
            'tier_currency' => 'USD',
        ]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/me/creator-profile/tiers/{$tier->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tiers', ['id' => $tier->id]);
    }

    public function test_destroy_returns_404_for_other_creators_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        ['user' => $other] = $this->createCreatorProfile();
        Sanctum::actingAs($other);

        $this->deleteJson("/api/me/creator-profile/tiers/{$tiers[2]->id}")
            ->assertNotFound();
    }
}
