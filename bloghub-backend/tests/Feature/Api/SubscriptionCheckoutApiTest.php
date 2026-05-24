<?php

namespace Tests\Feature\Api;

use App\Enums\SubStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Stripe\Checkout\Session as StripeSession;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class SubscriptionCheckoutApiTest extends TestCase
{
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_checkout_endpoints_require_authentication(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();

        $this->postJson('/api/subscriptions/create-checkout-session', ['tier_id' => $tiers[2]->id])
            ->assertUnauthorized();
        $this->postJson('/api/subscriptions/confirm-checkout', ['session_id' => 'cs_test_123'])
            ->assertUnauthorized();
    }

    public function test_free_tier_checkout_creates_subscription_immediately(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/subscriptions/create-checkout-session', [
            'tier_id' => $tiers[1]->id,
        ]);

        $res->assertOk()
            ->assertJsonPath('type', 'free')
            ->assertJsonPath('subscription.tier_id', $tiers[1]->id)
            ->assertJsonPath('subscription.sub_status', SubStatus::Active->value);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tiers[1]->id,
            'sub_status' => SubStatus::Active->value,
        ]);
    }

    public function test_paid_tier_checkout_returns_checkout_url(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $session = StripeSession::constructFrom([
            'id' => 'cs_test_checkout_123',
            'object' => 'checkout.session',
            'url' => 'https://checkout.stripe.com/pay/cs_test_checkout_123',
        ]);

        $this->mock(StripePaymentService::class, function ($mock) use ($user, $tiers, $profile, $session): void {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->withArgs(function ($passedUser, $passedTier, $successUrl, $cancelUrl) use ($user, $tiers, $profile) {
                    return $passedUser->id === $user->id
                        && $passedTier->id === $tiers[2]->id
                        && str_contains($successUrl, '/creator/' . $profile->slug)
                        && str_contains($cancelUrl, '/creator/' . $profile->slug);
                })
                ->andReturn($session);
        });

        $this->postJson('/api/subscriptions/create-checkout-session', [
            'tier_id' => $tiers[2]->id,
        ])
            ->assertOk()
            ->assertJsonPath('type', 'checkout')
            ->assertJsonPath('checkout_url', 'https://checkout.stripe.com/pay/cs_test_checkout_123');
    }

    public function test_checkout_returns_upgrade_confirm_when_subscribed_to_lower_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions/create-checkout-session', [
            'tier_id' => $tiers[3]->id,
        ])
            ->assertOk()
            ->assertJsonPath('type', 'upgrade_confirm')
            ->assertJsonPath('current_subscription.tier_name', $tiers[1]->tier_name)
            ->assertJsonPath('new_tier_name', $tiers[3]->tier_name);
    }

    public function test_checkout_returns_already_subscribed_when_requesting_lower_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[3]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions/create-checkout-session', [
            'tier_id' => $tiers[2]->id,
        ])
            ->assertOk()
            ->assertJsonPath('type', 'already_subscribed');
    }

    public function test_checkout_with_confirm_upgrade_proceeds_to_stripe_for_paid_tier(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        Sanctum::actingAs($user);

        $session = StripeSession::constructFrom([
            'id' => 'cs_test_upgrade_123',
            'object' => 'checkout.session',
            'url' => 'https://checkout.stripe.com/pay/cs_test_upgrade_123',
        ]);

        $this->mock(StripePaymentService::class, function ($mock) use ($session): void {
            $mock->shouldReceive('createCheckoutSession')->once()->andReturn($session);
        });

        $this->postJson('/api/subscriptions/create-checkout-session', [
            'tier_id' => $tiers[3]->id,
            'confirm_upgrade' => true,
        ])
            ->assertOk()
            ->assertJsonPath('type', 'checkout')
            ->assertJsonPath('checkout_url', 'https://checkout.stripe.com/pay/cs_test_upgrade_123');
    }

    public function test_confirm_checkout_returns_active_subscription(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
            'stripe_checkout_session_id' => 'cs_test_confirm_123',
        ]);
        $subscription->load(['tier', 'tier.creatorProfile']);
        Sanctum::actingAs($user);

        $this->mock(StripePaymentService::class, function ($mock) use ($user, $subscription): void {
            $mock->shouldReceive('getCheckoutSessionStatus')
                ->once()
                ->with('cs_test_confirm_123', $user->id)
                ->andReturn(['status' => 'active', 'subscription' => $subscription]);
        });

        $this->postJson('/api/subscriptions/confirm-checkout', [
            'session_id' => 'cs_test_confirm_123',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('subscription.tier_id', $tiers[2]->id);
    }

    public function test_confirm_checkout_returns_webhook_unavailable_when_paid_but_not_processed(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(StripePaymentService::class, function ($mock) use ($user): void {
            $mock->shouldReceive('getCheckoutSessionStatus')
                ->once()
                ->with('cs_test_pending_123', $user->id)
                ->andReturn(['status' => 'webhook_unavailable']);
        });

        $this->postJson('/api/subscriptions/confirm-checkout', [
            'session_id' => 'cs_test_pending_123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'webhook_unavailable');
    }

    public function test_confirm_checkout_returns_unpaid_for_unpaid_session(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(StripePaymentService::class, function ($mock) use ($user): void {
            $mock->shouldReceive('getCheckoutSessionStatus')
                ->once()
                ->with('cs_test_unpaid_123', $user->id)
                ->andReturn(['status' => 'unpaid']);
        });

        $this->postJson('/api/subscriptions/confirm-checkout', [
            'session_id' => 'cs_test_unpaid_123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'unpaid');
    }

    public function test_confirm_checkout_returns_invalid_for_wrong_user_session(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->mock(StripePaymentService::class, function ($mock): void {
            $mock->shouldReceive('getCheckoutSessionStatus')
                ->once()
                ->andReturn(['status' => 'invalid']);
        });

        $this->postJson('/api/subscriptions/confirm-checkout', [
            'session_id' => 'cs_test_other_user_123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'invalid');
    }
}
