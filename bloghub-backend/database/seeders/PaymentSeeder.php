<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = Subscription::with('tier')->get();

        if ($subscriptions->isEmpty()) {
            $this->command->warn('No subscriptions found. Run SubscriptionSeeder first.');

            return;
        }

        $currencies = Currency::cases();
        $statuses = PaymentStatus::cases();

        foreach ($subscriptions as $subscription) {
            $existingCount = $subscription->payments()->count();
            $paymentCount = $existingCount > 0 ? 0 : rand(1, 3);

            for ($i = 0; $i < $paymentCount; $i++) {
                $currency = $subscription->tier?->tier_currency ?? $currencies[array_rand($currencies)];
                $amount = $subscription->tier?->price ?? rand(5, 500);
                $checkoutDate = $subscription->start_date->copy()->addDays($i * 7 + rand(0, 6));

                Payment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'checkout_date' => $checkoutDate,
                    'card_last4' => (string) rand(1000, 9999),
                    'payment_status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
