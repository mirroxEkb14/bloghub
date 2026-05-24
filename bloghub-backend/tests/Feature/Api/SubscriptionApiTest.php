<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    public function test_subscription_endpoints_require_authentication(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $subscription = Subscription::query()->create([
            'user_id' => User::factory()->create()->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);

        $this->getJson('/api/me/subscriptions')->assertUnauthorized();
        $this->postJson('/api/subscriptions', ['tier_id' => $tiers[1]->id])->assertUnauthorized();
        $this->getJson("/api/creator-profiles/{$profile->slug}/subscription-status")->assertUnauthorized();
        $this->patchJson("/api/subscriptions/{$subscription->id}/cancel")->assertUnauthorized();
    }

    public function test_store_creates_active_subscription_for_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/subscriptions', ['tier_id' => $tiers[2]->id]);

        $res->assertCreated()
            ->assertJsonPath('data.tier_id', $tiers[2]->id)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.sub_status', SubStatus::Active->value)
            ->assertJsonStructure(['data' => ['id', 'start_date', 'end_date', 'tier']]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'sub_status' => SubStatus::Active->value,
        ]);
    }

    public function test_store_validates_tier_id(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/subscriptions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tier_id']);

        $this->postJson('/api/subscriptions', ['tier_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tier_id']);
    }

    public function test_index_lists_authenticated_user_subscriptions(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $res = $this->getJson('/api/me/subscriptions');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $subscription->id)
            ->assertJsonPath('data.0.tier.id', $tiers[2]->id)
            ->assertJsonPath('data.0.creator.slug', $profile->slug);
    }

    public function test_index_includes_latest_payment_card_last4(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Payment::query()->create([
            'subscription_id' => $subscription->id,
            'amount' => 10,
            'currency' => 'USD',
            'checkout_date' => now()->subHour(),
            'card_last4' => '1111',
            'payment_status' => PaymentStatus::Completed,
        ]);
        Payment::query()->create([
            'subscription_id' => $subscription->id,
            'amount' => 10,
            'currency' => 'USD',
            'checkout_date' => now(),
            'card_last4' => '4242',
            'payment_status' => PaymentStatus::Completed,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/subscriptions')
            ->assertOk()
            ->assertJsonPath('data.0.card_last4', '4242');
    }

    public function test_status_by_creator_returns_not_subscribed_when_no_subscription(): void
    {
        ['profile' => $profile] = $this->createCreatorWithTiers();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/creator-profiles/{$profile->slug}/subscription-status")
            ->assertOk()
            ->assertJsonPath('subscribed', false)
            ->assertJsonPath('active_subscription', null);
    }

    public function test_status_by_creator_returns_highest_active_subscription(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $lower = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addDay(),
            'sub_status' => SubStatus::Canceled,
        ]);
        Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[3]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/creator-profiles/{$profile->slug}/subscription-status")
            ->assertOk()
            ->assertJsonPath('subscribed', true)
            ->assertJsonPath('active_subscription.tier_id', $tiers[3]->id);

        $this->assertNotSame($lower->tier_id, $tiers[3]->id);
    }

    public function test_status_by_creator_returns_404_for_unknown_creator(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/creator-profiles/unknown-creator/subscription-status')
            ->assertNotFound();
    }

    public function test_cancel_marks_subscription_canceled(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'sub_status' => SubStatus::Active,
        ]);
        $expectedEndDate = $subscription->fresh()->end_date->format('Y-m-d H:i:s');
        Sanctum::actingAs($user);

        $this->patchJson("/api/subscriptions/{$subscription->id}/cancel")
            ->assertOk()
            ->assertJsonPath('subscription.sub_status', SubStatus::Canceled->value);

        $subscription->refresh();
        $this->assertSame(SubStatus::Canceled, $subscription->sub_status);
        $this->assertSame($expectedEndDate, $subscription->end_date->format('Y-m-d H:i:s'));
        $this->assertTrue($subscription->end_date->isFuture());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'subscription_canceled',
        ]);
    }

    public function test_cancel_with_end_now_expires_immediately(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/subscriptions/{$subscription->id}/cancel", ['end_now' => true])
            ->assertOk();

        $subscription->refresh();
        $this->assertSame(SubStatus::Canceled, $subscription->sub_status);
        $this->assertTrue($subscription->end_date->lte(now()));
    }

    public function test_cancel_forbidden_for_other_users_subscription(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $owner->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($other);

        $this->patchJson("/api/subscriptions/{$subscription->id}/cancel")
            ->assertForbidden();
    }
}
