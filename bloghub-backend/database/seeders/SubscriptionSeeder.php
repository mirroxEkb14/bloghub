<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Models\CreatorProfile;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    private const SUBSCRIPTION_YEARS = [2019, 2019, 2021, 2021, 2023, 2023, 2024, 2024, 2024, 2025, 2025, 2026];
    private array $usersByName = [];
    private array $profilesByUserName = [];
    private array $tiersByCreatorAndName = [];

    public function run(): void
    {
        $this->loadUsersAndProfiles();
        $this->loadTiers();

        $subscriptionsMatrix = $this->buildSubscriptionsMatrix();

        foreach ($subscriptionsMatrix as [$subscriberName, $creatorName, $tierName]) {
            $this->createSubscriptionWithCompletedPayment($subscriberName, $creatorName, $tierName);
        }

        $this->createExtraSubscriptionsWithPendingOrFailedPayments();
    }

    private function randomDateTimeInYear(int $year): Carbon
    {
        $month = rand(1, 12);
        $day = rand(1, min(28, (int) date('t', mktime(0, 0, 0, $month, 1, $year))));
        $hour = rand(0, 23);
        $minute = rand(0, 59);
        $second = rand(0, 59);

        return Carbon::create($year, $month, $day, $hour, $minute, $second);
    }

    private function pickRandomYear(): int
    {
        return self::SUBSCRIPTION_YEARS[array_rand(self::SUBSCRIPTION_YEARS)];
    }

    private function loadUsersAndProfiles(): void
    {
        $names = [
            'Fox Mulder', 'Dana Scully', 'Gordon Freeman', 'Gregory House', 'Caroline',
            'Ellen Ripley', 'Maggie Rhee', 'Negan', 'Carl Johnson', 'Thomas A. Anderson', 'Tiffany Zion',
        ];
        foreach ($names as $name) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $this->usersByName[$name] = $user;
                $profile = CreatorProfile::where('user_id', $user->id)->first();
                if ($profile) {
                    $this->profilesByUserName[$name] = $profile;
                }
            }
        }
    }

    private function loadTiers(): void
    {
        $tiers = Tier::with('creatorProfile.user')->get();
        foreach ($tiers as $tier) {
            $creatorName = $tier->creatorProfile->user->name ?? null;
            if ($creatorName) {
                $this->tiersByCreatorAndName[$creatorName . '|' . $tier->tier_name] = $tier;
            }
        }
    }

    private function buildSubscriptionsMatrix(): array
    {
        $list = [];

        foreach (['Carl Johnson', 'Maggie Rhee', 'Negan', 'Tiffany Zion'] as $name) {
            $list[] = [$name, 'Gordon Freeman', 'Research Associate (Clearance 3)'];
        }
        foreach (['Fox Mulder', 'Dana Scully', 'Ellen Ripley'] as $name) {
            $list[] = [$name, 'Gordon Freeman', 'The Anti-Citizen'];
        }
        $list[] = ['Gregory House', 'Gordon Freeman', 'The One Free Man'];
        $list[] = ['Caroline', 'Gordon Freeman', 'The One Free Man'];
        $list[] = ['Thomas A. Anderson', 'Gordon Freeman', 'The One Free Man'];

        foreach (['Dana Scully', 'Gordon Freeman', 'Ellen Ripley'] as $name) {
            $list[] = [$name, 'Fox Mulder', 'The Conspirator'];
        }
        foreach (['Thomas A. Anderson', 'Tiffany Zion'] as $name) {
            $list[] = [$name, 'Fox Mulder', 'The Abductee'];
        }
        $list[] = ['Negan', 'Fox Mulder', 'The Believer'];

        foreach (['Dana Scully', 'Fox Mulder'] as $name) {
            $list[] = [$name, 'Ellen Ripley', 'The Alien'];
        }
        $list[] = ['Caroline', 'Ellen Ripley', 'The Captain'];
        foreach (['Carl Johnson', 'Maggie Rhee', 'Negan'] as $name) {
            $list[] = [$name, 'Ellen Ripley', 'Sole Survivor'];
        }

        $list[] = ['Fox Mulder', 'Dana Scully', 'FBI Liaison'];
        foreach (['Ellen Ripley', 'Gregory House'] as $name) {
            $list[] = [$name, 'Dana Scully', 'Investigator'];
        }
        foreach (['Caroline', 'Gordon Freeman', 'Maggie Rhee'] as $name) {
            $list[] = [$name, 'Dana Scully', 'Agent'];
        }

        foreach (['Gregory House', 'Gordon Freeman', 'Negan'] as $name) {
            $list[] = [$name, 'Caroline', 'Central AI Overseer'];
        }
        foreach (['Thomas A. Anderson', 'Tiffany Zion'] as $name) {
            $list[] = [$name, 'Caroline', 'Maintenance Specialist (Level 4)'];
        }
        foreach (['Fox Mulder', 'Dana Scully', 'Ellen Ripley', 'Maggie Rhee', 'Carl Johnson'] as $name) {
            $list[] = [$name, 'Caroline', 'Test Subject #1498'];
        }

        $list[] = ['Negan', 'Maggie Rhee', 'The Bricks Leader'];
        foreach (['Fox Mulder', 'Dana Scully'] as $name) {
            $list[] = [$name, 'Maggie Rhee', 'Hilltop Chosen'];
        }

        $list[] = ['Maggie Rhee', 'Negan', 'The Burazi Leader'];
        foreach (['Gregory House', 'Dana Scully'] as $name) {
            $list[] = [$name, 'Negan', 'The Alexandria Prisoner'];
        }
        foreach (['Fox Mulder', 'Gordon Freeman', 'Ellen Ripley'] as $name) {
            $list[] = [$name, 'Negan', 'The Sanctuary Savior'];
        }

        return $list;
    }

    private function createSubscriptionWithCompletedPayment(string $subscriberName, string $creatorName, string $tierName): void
    {
        $user = $this->usersByName[$subscriberName] ?? null;
        $tier = $this->tiersByCreatorAndName[$creatorName . '|' . $tierName] ?? null;
        if (! $user || ! $tier) {
            return;
        }

        $year = $this->pickRandomYear();
        $startDate = $this->randomDateTimeInYear($year);
        $endDate = $startDate->copy()->addMonth();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sub_status' => SubStatus::Active,
        ]);

        $daysBetween = (int) $startDate->diffInDays($endDate);
        $checkoutDate = $startDate->copy()->addDays(rand(0, min(3, max(0, $daysBetween - 1))));
        $checkoutDate->setTime(rand(0, 23), rand(0, 59), rand(0, 59));

        Payment::create([
            'subscription_id' => $sub->id,
            'amount' => $tier->price,
            'currency' => $tier->tier_currency,
            'checkout_date' => $checkoutDate,
            'card_last4' => '4242',
            'payment_status' => PaymentStatus::Completed,
        ]);
    }

    private function createExtraSubscriptionsWithPendingOrFailedPayments(): void
    {
        $usedKeys = $this->getUsedSubscriptionKeys();
        $candidates = $this->getExtraSubscriptionCandidates($usedKeys);
        if (count($candidates) < 2) {
            return;
        }

        $completedCount = Payment::where('payment_status', PaymentStatus::Completed)->count();
        $numPending = min(8, (int) floor($completedCount / 2));
        $numFailed = min(15, $completedCount - 1);
        if ($numPending === $numFailed) {
            $numFailed--;
        }

        shuffle($candidates);
        $pendingCandidates = array_slice($candidates, 0, $numPending);
        $failedCandidates = array_slice($candidates, $numPending, $numFailed);

        foreach ($pendingCandidates as $c) {
            $this->createExtraSubscriptionWithPayment($c[0], $c[1], $c[2], PaymentStatus::Pending);
        }
        foreach ($failedCandidates as $c) {
            $this->createExtraSubscriptionWithPayment($c[0], $c[1], $c[2], PaymentStatus::Failed);
        }
    }

    private function getUsedSubscriptionKeys(): array
    {
        $keys = [];
        foreach (Subscription::with('payments')->get() as $sub) {
            $keys[$sub->user_id . '|' . $sub->tier_id] = true;
        }
        return $keys;
    }

    private function getExtraSubscriptionCandidates(array $usedKeys): array
    {
        $candidates = [];
        $creatorNames = array_keys($this->profilesByUserName);
        $tierListByCreator = [];
        foreach ($this->tiersByCreatorAndName as $key => $tier) {
            [$creatorName,] = explode('|', $key, 2);
            $tierListByCreator[$creatorName][] = $tier;
        }

        foreach ($this->usersByName as $subscriberName => $user) {
            foreach ($creatorNames as $creatorName) {
                if ($subscriberName === $creatorName) {
                    continue;
                }
                $tiers = $tierListByCreator[$creatorName] ?? [];
                foreach ($tiers as $tier) {
                    $k = $user->id . '|' . $tier->id;
                    if (empty($usedKeys[$k])) {
                        $tierName = $tier->tier_name;
                        $candidates[] = [$subscriberName, $creatorName, $tierName];
                        $usedKeys[$k] = true;
                    }
                }
            }
        }
        return $candidates;
    }

    private function createExtraSubscriptionWithPayment(string $subscriberName, string $creatorName, string $tierName, PaymentStatus $status): void
    {
        $user = $this->usersByName[$subscriberName] ?? null;
        $tier = $this->tiersByCreatorAndName[$creatorName . '|' . $tierName] ?? null;
        if (! $user || ! $tier) {
            return;
        }

        $year = $this->pickRandomYear();
        $startDate = $this->randomDateTimeInYear($year);
        $endDate = $startDate->copy()->addMonth();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sub_status' => SubStatus::Active,
        ]);

        $checkoutDate = $startDate->copy()->addDays(rand(0, 3));
        $checkoutDate->setTime(rand(0, 23), rand(0, 59), rand(0, 59));

        Payment::create([
            'subscription_id' => $sub->id,
            'amount' => $tier->price,
            'currency' => $tier->tier_currency,
            'checkout_date' => $checkoutDate,
            'card_last4' => '4242',
            'payment_status' => $status,
        ]);
    }
}
