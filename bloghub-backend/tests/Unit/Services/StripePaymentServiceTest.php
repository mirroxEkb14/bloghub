<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class StripePaymentServiceTest extends TestCase
{
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_checkout_session_creates_stripe_customer_when_missing(): void
    {
        ['creator' => $user, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $user->update(['stripe_customer_id' => null]);
        $tier = $tiers[2];
        $tier->load('creatorProfile');

        $customers = Mockery::mock();
        $customers->shouldReceive('create')
            ->once()
            ->with(['email' => $user->email, 'name' => $user->name])
            ->andReturn((object) ['id' => 'cus_test_123']);

        $checkoutSessions = Mockery::mock();
        $checkoutSessions->shouldReceive('create')
            ->once()
            ->withArgs(function (array $params) use ($user, $tier) {
                return $params['customer'] === 'cus_test_123'
                    && $params['client_reference_id'] === (string) $user->id
                    && $params['metadata']['tier_id'] === (string) $tier->id
                    && $params['line_items'][0]['price_data']['unit_amount'] === (int) round($tier->price * 100);
            })
            ->andReturn(StripeSession::constructFrom([
                'id' => 'cs_test_create_123',
                'object' => 'checkout.session',
                'url' => 'https://checkout.stripe.com/test',
            ]));

        $stripe = $this->mockStripeClient($customers, $checkoutSessions);
        $service = new StripePaymentService($stripe);

        $session = $service->createCheckoutSession(
            $user->fresh(),
            $tier,
            'https://example.com/success',
            'https://example.com/cancel',
        );

        $this->assertSame('cs_test_create_123', $session->id);
        $this->assertSame('cus_test_123', $user->fresh()->stripe_customer_id);
    }

    public function test_create_checkout_session_reuses_existing_stripe_customer(): void
    {
        ['creator' => $user, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $user->update(['stripe_customer_id' => 'cus_existing']);
        $tier = $tiers[1];
        $tier->load('creatorProfile');

        $customers = Mockery::mock();
        $customers->shouldNotReceive('create');

        $checkoutSessions = Mockery::mock();
        $checkoutSessions->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['customer'] === 'cus_existing')
            ->andReturn(StripeSession::constructFrom(['id' => 'cs_test_456', 'object' => 'checkout.session']));

        $service = new StripePaymentService($this->mockStripeClient(null, $checkoutSessions));
        $session = $service->createCheckoutSession($user, $tier, 'https://example.com/success', 'https://example.com/cancel');

        $this->assertSame('cs_test_456', $session->id);
    }

    public function test_get_checkout_session_status_returns_invalid_for_bad_session_id(): void
    {
        $service = new StripePaymentService($this->mockStripeClient());

        $this->assertSame(['status' => 'invalid'], $service->getCheckoutSessionStatus('', 1));
        $this->assertSame(['status' => 'invalid'], $service->getCheckoutSessionStatus('pi_wrong', 1));
    }

    public function test_get_checkout_session_status_returns_unpaid_when_not_paid(): void
    {
        $user = User::factory()->create();
        $checkoutSessions = Mockery::mock();
        $checkoutSessions->shouldReceive('retrieve')
            ->once()
            ->with('cs_test_unpaid')
            ->andReturn(StripeSession::constructFrom([
                'id' => 'cs_test_unpaid',
                'object' => 'checkout.session',
                'payment_status' => 'unpaid',
                'client_reference_id' => (string) $user->id,
            ]));

        $service = new StripePaymentService($this->mockStripeClient(null, $checkoutSessions));

        $this->assertSame(['status' => 'unpaid'], $service->getCheckoutSessionStatus('cs_test_unpaid', $user->id));
    }

    public function test_get_checkout_session_status_returns_active_when_subscription_exists(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $sessionId = 'cs_test_active_123';
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
            'stripe_checkout_session_id' => $sessionId,
        ]);

        $checkoutSessions = Mockery::mock();
        $checkoutSessions->shouldReceive('retrieve')
            ->once()
            ->andReturn(StripeSession::constructFrom([
                'id' => $sessionId,
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'client_reference_id' => (string) $user->id,
            ]));

        $service = new StripePaymentService($this->mockStripeClient(null, $checkoutSessions));
        $result = $service->getCheckoutSessionStatus($sessionId, $user->id);

        $this->assertSame('active', $result['status']);
        $this->assertSame($subscription->id, $result['subscription']->id);
    }

    public function test_get_checkout_session_status_returns_webhook_unavailable_when_paid_but_no_subscription(): void
    {
        $user = User::factory()->create();
        $checkoutSessions = Mockery::mock();
        $checkoutSessions->shouldReceive('retrieve')
            ->once()
            ->andReturn(StripeSession::constructFrom([
                'id' => 'cs_test_pending',
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'client_reference_id' => (string) $user->id,
            ]));

        $service = new StripePaymentService($this->mockStripeClient(null, $checkoutSessions));

        $this->assertSame(
            ['status' => 'webhook_unavailable'],
            $service->getCheckoutSessionStatus('cs_test_pending', $user->id)
        );
    }

    public function test_handle_checkout_session_completed_creates_subscription_and_payment(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $sessionId = 'cs_test_unit_' . fake()->unique()->uuid();

        $session = StripeSession::constructFrom([
            'id' => $sessionId,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'payment_status' => 'paid',
            'amount_total' => 1000,
            'client_reference_id' => (string) $user->id,
            'metadata' => [
                'tier_id' => (string) $tiers[2]->id,
                'user_id' => (string) $user->id,
            ],
        ]);

        $service = new StripePaymentService($this->mockStripeClient());
        $service->handleCheckoutSessionCompleted($session);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'stripe_checkout_session_id' => $sessionId,
            'sub_status' => SubStatus::Active->value,
        ]);

        $subscription = Subscription::query()->where('stripe_checkout_session_id', $sessionId)->firstOrFail();
        $this->assertDatabaseHas('payments', [
            'subscription_id' => $subscription->id,
            'amount' => 10,
            'payment_status' => PaymentStatus::Completed->value,
        ]);
    }

    public function test_handle_checkout_session_completed_is_idempotent(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $sessionId = 'cs_test_idempotent_' . fake()->unique()->uuid();

        $session = StripeSession::constructFrom([
            'id' => $sessionId,
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'amount_total' => 1000,
            'client_reference_id' => (string) $user->id,
            'metadata' => ['tier_id' => (string) $tiers[1]->id],
        ]);

        $service = new StripePaymentService($this->mockStripeClient());
        $service->handleCheckoutSessionCompleted($session);
        $service->handleCheckoutSessionCompleted($session);

        $this->assertSame(1, Subscription::query()->where('stripe_checkout_session_id', $sessionId)->count());
        $this->assertSame(1, Payment::query()->where('stripe_checkout_session_id', $sessionId)->count());
    }

    private function mockStripeClient(?Mockery\MockInterface $customers = null, ?Mockery\MockInterface $checkoutSessions = null): StripeClient
    {
        $stripe = Mockery::mock(StripeClient::class);
        if ($customers !== null) {
            $stripe->customers = $customers;
        }
        if ($checkoutSessions !== null) {
            $stripe->checkout = (object) ['sessions' => $checkoutSessions];
        }

        return $stripe;
    }
}
