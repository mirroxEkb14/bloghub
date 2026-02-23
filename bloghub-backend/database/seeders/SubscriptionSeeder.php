<?php

namespace Database\Seeders;

use App\Enums\SubStatus;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('name', 'User')->first();
        $admin = User::where('name', 'Admin')->first();
        $superAdmin = User::where('name', 'Super Admin')->first();

        if (! $user || ! $admin || ! $superAdmin) {
            $this->command->warn('Users (User, Admin, Super Admin) not found. Run UsersSeeder first.');

            return;
        }

        $tiers = Tier::with('creatorProfile.user')->get();
        if ($tiers->isEmpty()) {
            $this->command->warn('No tiers found. Run TierSeeder first.');

            return;
        }

        $subscriptions = [
            [
                'user_name' => 'User',
                'tier_creator_name' => 'Admin',
                'tier_level' => 1,
                'sub_status' => SubStatus::Active,
                'end_date_offset_months' => 3,
            ],
            [
                'user_name' => 'User',
                'tier_creator_name' => 'Super Admin',
                'tier_level' => 2,
                'sub_status' => SubStatus::Active,
                'end_date_offset_months' => 6,
            ],
            [
                'user_name' => 'Admin',
                'tier_creator_name' => 'User',
                'tier_level' => 1,
                'sub_status' => SubStatus::Active,
                'end_date_offset_months' => 1,
            ],
            [
                'user_name' => 'Admin',
                'tier_creator_name' => 'Super Admin',
                'tier_level' => 3,
                'sub_status' => SubStatus::Canceled,
                'end_date_offset_months' => 1,
                'start_date_offset_days' => -60,
            ],
            [
                'user_name' => 'Super Admin',
                'tier_creator_name' => 'User',
                'tier_level' => 2,
                'sub_status' => SubStatus::Active,
                'end_date_offset_months' => 12,
            ],
        ];

        foreach ($subscriptions as $data) {
            $subscriber = User::where('name', $data['user_name'])->first();
            $tier = Tier::where('level', $data['tier_level'])
                ->whereHas('creatorProfile.user', fn ($q) => $q->where('name', $data['tier_creator_name']))
                ->first();

            if (! $subscriber || ! $tier) {
                continue;
            }

            $startDate = isset($data['start_date_offset_days'])
                ? now()->addDays($data['start_date_offset_days'])
                : now()->subDays(rand(1, 60));
            $endDate = $startDate->copy()->addMonths($data['end_date_offset_months']);

            Subscription::firstOrCreate(
                [
                    'user_id' => $subscriber->id,
                    'tier_id' => $tier->id,
                ],
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'sub_status' => $data['sub_status'],
                ]
            );
        }
    }
}
