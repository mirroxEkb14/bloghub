<?php

namespace Database\Seeders;

use App\Models\CreatorProfile;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CreatorProfileFollowSeeder extends Seeder
{
    private const FOLLOW_START_YEAR = 2019;

    private const FOLLOWS = [
        'Caroline' => ['Gordon Freeman', 'Ellen Ripley', 'Dana Scully', 'Maggie Rhee', 'Trinity Zion'],
        'Dana Scully' => ['Fox Mulder', 'Ellen Ripley', 'Gregory House', 'Thomas A. Anderson', 'Trinity Zion', 'Carl Johnson'],
        'Ellen Ripley' => ['Dana Scully', 'Fox Mulder', 'Gregory House', 'Maggie Rhee', 'Negan', 'Trinity Zion'],
        'Fox Mulder' => ['Dana Scully', 'Caroline', 'Gordon Freeman', 'Ellen Ripley', 'Gregory House', 'Negan', 'Thomas A. Anderson', 'Carl Johnson'],
        'Gordon Freeman' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Fox Mulder', 'Gregory House', 'Maggie Rhee', 'Negan', 'Thomas A. Anderson', 'Trinity Zion', 'Carl Johnson'],
        'Gregory House' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Gordon Freeman', 'Maggie Rhee'],
        'Maggie Rhee' => ['Dana Scully', 'Fox Mulder', 'Negan', 'Carl Johnson'],
        'Negan' => ['Fox Mulder', 'Gordon Freeman', 'Gregory House', 'Maggie Rhee', 'Carl Johnson', 'Thomas A. Anderson'],
    ];

    public function run(): void
    {
        $usersByName = User::all()->keyBy('name');
        $profilesByDisplayName = CreatorProfile::all()->keyBy('display_name');

        $earliestSub = Subscription::query()->min('created_at');
        $rangeStart = $earliestSub
            ? Carbon::parse($earliestSub)->subMonths(6)
            : Carbon::create(self::FOLLOW_START_YEAR, 1, 1)->startOfDay();
        $rangeEnd = now();

        foreach (self::FOLLOWS as $creatorDisplayName => $followerNames) {
            $profile = $profilesByDisplayName->get($creatorDisplayName);
            if (! $profile) {
                $this->command->warn("Creator profile \"{$creatorDisplayName}\" not found, skipping follows");

                continue;
            }

            foreach ($followerNames as $followerName) {
                $user = $usersByName->get($followerName);
                if (! $user) {
                    $this->command->warn("User \"{$followerName}\" not found, skipping follow for {$creatorDisplayName}");

                    continue;
                }

                if ($user->id === $profile->user_id) {
                    continue;
                }

                if ($profile->followers()->where('users.id', $user->id)->exists()) {
                    continue;
                }

                $followedAt = $this->randomDateTimeBetween($rangeStart, $rangeEnd);
                $profile->followers()->attach($user->id, [
                    'created_at' => $followedAt,
                    'updated_at' => $followedAt,
                ]);
            }
        }
    }

    private function randomDateTimeBetween(Carbon $start, Carbon $end): Carbon
    {
        $ts = rand($start->timestamp, $end->timestamp);

        return Carbon::createFromTimestamp($ts);
    }
}
