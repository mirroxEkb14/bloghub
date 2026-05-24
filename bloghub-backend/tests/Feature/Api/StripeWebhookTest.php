<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_webhook_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => []]]);

        $this->call(
            'POST',
            '/api/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => 't=0,v1=invalid',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        )->assertStatus(400);
    }

    public function test_webhook_creates_subscription_and_payment_on_checkout_completed(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $sessionId = 'cs_test_webhook_' . fake()->unique()->uuid();

        $event = [
            'id' => 'evt_test_' . fake()->unique()->uuid(),
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
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
                ],
            ],
        ];

        $this->postStripeWebhook($event)->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tiers[2]->id,
            'stripe_checkout_session_id' => $sessionId,
            'sub_status' => SubStatus::Active->value,
        ]);

        $subscription = Subscription::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->firstOrFail();

        $this->assertDatabaseHas('payments', [
            'subscription_id' => $subscription->id,
            'stripe_checkout_session_id' => $sessionId,
            'amount' => 10,
            'payment_status' => PaymentStatus::Completed->value,
        ]);
    }

    public function test_webhook_cancels_previous_subscription_on_upgrade(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $previous = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
        $sessionId = 'cs_test_webhook_upgrade_' . fake()->unique()->uuid();

        $event = [
            'id' => 'evt_test_' . fake()->unique()->uuid(),
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'mode' => 'payment',
                    'payment_status' => 'paid',
                    'amount_total' => 2000,
                    'client_reference_id' => (string) $user->id,
                    'metadata' => [
                        'tier_id' => (string) $tiers[3]->id,
                        'user_id' => (string) $user->id,
                    ],
                ],
            ],
        ];

        $this->postStripeWebhook($event)->assertOk();

        $previous->refresh();
        $this->assertSame(SubStatus::Canceled, $previous->sub_status);
        $this->assertTrue($previous->end_date->lte(now()));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tiers[3]->id,
            'stripe_checkout_session_id' => $sessionId,
            'sub_status' => SubStatus::Active->value,
        ]);
    }

    public function test_webhook_is_idempotent_for_duplicate_checkout_session(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $user = User::factory()->create();
        $sessionId = 'cs_test_webhook_dup_' . fake()->unique()->uuid();

        $event = [
            'id' => 'evt_test_' . fake()->unique()->uuid(),
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
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
                ],
            ],
        ];

        $this->postStripeWebhook($event)->assertOk();
        $this->postStripeWebhook($event)->assertOk();

        $this->assertSame(
            1,
            Subscription::query()->where('stripe_checkout_session_id', $sessionId)->count()
        );
        $this->assertSame(
            1,
            Payment::query()->where('stripe_checkout_session_id', $sessionId)->count()
        );
    }

    private function postStripeWebhook(array $event): TestResponse
    {
        $payload = json_encode($event);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, self::WEBHOOK_SECRET);

        return $this->call(
            'POST',
            '/api/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );
    }
}
