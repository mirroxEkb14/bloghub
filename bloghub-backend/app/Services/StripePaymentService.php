<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;

class StripePaymentService
{
    public function __construct(
        protected StripeClient $stripe
    ) {}

    public function createCheckoutSession(User $user, Tier $tier, string $successUrl, string $cancelUrl): StripeSession
    {
        $customerId = $user->stripe_customer_id;
        if (! $customerId) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $customerId = $customer->id;
            $user->update(['stripe_customer_id' => $customerId]);
        }

        $currency = strtolower($tier->tier_currency->value);
        $unitAmount = (int) round($tier->price * 100);

        $session = $this->stripe->checkout->sessions->create([
            'customer' => $customerId,
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $tier->tier_name,
                            'description' => $tier->tier_desc ?? null,
                            'metadata' => [
                                'tier_id' => (string) $tier->id,
                            ],
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $user->id,
            'metadata' => [
                'tier_id' => (string) $tier->id,
                'user_id' => (string) $user->id,
                'creator_slug' => $tier->creatorProfile->slug ?? '',
            ],
        ]);

        return $session;
    }

    public function handleCheckoutSessionCompleted(StripeSession $session): void
    {
        $sessionId = $session->id;
        if (Subscription::query()->where('stripe_checkout_session_id', $sessionId)->exists()) {
            return;
        }

        $tierId = (int) ($session->metadata['tier_id'] ?? 0);
        $userId = (int) ($session->client_reference_id ?? $session->metadata['user_id'] ?? 0);
        if (! $tierId || ! $userId) {
            return;
        }

        $tier = Tier::query()->find($tierId);
        $user = User::query()->find($userId);
        if (! $tier || ! $user) {
            return;
        }

        $subscription = DB::transaction(function () use ($session, $sessionId, $tier, $user) {
            $startDate = now();
            $endDate = $startDate->copy()->addMonth();

            $sub = Subscription::query()->create([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sub_status' => SubStatus::Active,
                'stripe_checkout_session_id' => $sessionId,
            ]);

            $amountCents = $session->amount_total ?? 0;
            $currency = $tier->tier_currency ?? Currency::USD;
            $cardLast4 = '';

            $paymentIntentId = is_string($session->payment_intent) ? $session->payment_intent : null;
            if ($paymentIntentId) {
                $pi = $this->stripe->paymentIntents->retrieve($paymentIntentId, [
                    'expand' => ['payment_method.card', 'charges.data.payment_method_details'],
                ]);
                if ($amountCents === 0) {
                    $amountCents = $pi->amount ?? 0;
                }
                $currency = $this->mapStripeCurrencyToEnum($pi->currency ?? 'usd');
                if ($pi->payment_method && isset($pi->payment_method->card->last4)) {
                    $cardLast4 = $pi->payment_method->card->last4;
                } elseif (($firstCharge = $pi->charges->data[0] ?? null) && isset($firstCharge->payment_method_details->card->last4)) {
                    $cardLast4 = $firstCharge->payment_method_details->card->last4;
                }
            }
            if ($amountCents === 0) {
                $amountCents = (int) round($tier->price * 100);
            }

            Payment::query()->create([
                'subscription_id' => $sub->id,
                'stripe_checkout_session_id' => $sessionId,
                'amount' => (int) round($amountCents / 100),
                'currency' => $currency,
                'checkout_date' => now(),
                'card_last4' => $cardLast4 ?: '0000',
                'payment_status' => PaymentStatus::Completed,
            ]);

            return $sub;
        });
    }

    private function mapStripeCurrencyToEnum(string $currency): Currency
    {
        $upper = strtoupper($currency);
        return match ($upper) {
            'EUR' => Currency::EUR,
            'CZK' => Currency::CZK,
            default => Currency::USD,
        };
    }
}
