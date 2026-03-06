<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostView;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PostLikeSeeder extends Seeder
{
    private const LIKES_BY_CREATOR = [
        'Caroline' => [
            'The Borealis: A Lesson in Spontaneous Relocation' => ['Gordon Freeman', 'Fox Mulder', 'Thomas A. Anderson', 'Tiffany Zion'],
            'The Black Mesa Anomaly: A Study in Incompetence' => ['Gordon Freeman', 'Ellen Ripley'],
            'The Cake: A Non-Existent Incentive' => null,
            'The Iterative Soul: From Caroline to Core' => ['Tiffany Zion'],
            'The Aperture Science Handheld Portal Device' => ['Gordon Freeman'],
        ],
        'Dana Scully' => [
            'Case File: 6x21 — The Brown Mountain Symbiosis' => ['Fox Mulder', 'Gregory House'],
            'Case File: 6x06 — The Holiday Solstice' => ['Fox Mulder', 'Negan', 'Carl Johnson'],
            'Einstein\'s Twin Paradox: A New Interpretation' => ['Caroline', 'Gordon Freeman', 'Ellen Ripley', 'Gregory House'],
        ],
        'Ellen Ripley' => [
            'XX121: The Predator Perfection' => ['Caroline', 'Dana Scully', 'Gregory House', 'Negan'],
            'The Acheron (LV-426) Site' => ['Dana Scully', 'Fox Mulder'],
            'Special Order 937' => ['Carl Johnson', 'Dana Scully', 'Fox Mulder'],
        ],
        'Fox Mulder' => [
            'The Blackwood Anomaly and the Texas Bio-Lobby' => ['Dana Scully', 'Ellen Ripley'],
            'The Mechanics of Abduction and Lost Time' => ['Thomas A. Anderson', 'Tiffany Zion', 'Dana Scully'],
        ],
        'Gordon Freeman' => [
            "The Universal 'Combine' Union" => ['Caroline', 'Dana Scully', 'Fox Mulder', 'Thomas A. Anderson', 'Tiffany Zion'],
            '"Xenocrystal Bloom" - Sound Insight' => ['Caroline'],
            'Resonance Cascade Event – Video Insight' => ['Caroline'],
            'Resonance Cascade Event' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Fox Mulder', 'Gregory House', 'Maggie Rhee', 'Negan', 'Thomas A. Anderson', 'Tiffany Zion', 'Carl Johnson'],
            'Borderworld Xen' => ['Carl Johnson', 'Maggie Rhee', 'Negan', 'Fox Mulder', 'Dana Scully', 'Ellen Ripley'],
        ],
        'Maggie Rhee' => [
            'From Survivor to Architect' => ['Gregory House', 'Negan'],
        ],
        'Negan' => [
            'A Retrospective on Staying Alive' => ['Gregory House', 'Maggie Rhee'],
        ],
    ];

    public function run(): void
    {
        foreach (self::LIKES_BY_CREATOR as $creatorUserName => $titleToLikers) {
            $creator = User::where('name', $creatorUserName)->first();
            if (! $creator) {
                continue;
            }
            $profile = $creator->creatorProfile;
            if (! $profile) {
                continue;
            }

            foreach ($titleToLikers as $postTitle => $likerNames) {
                $post = Post::where('creator_profile_id', $profile->id)
                    ->where('title', $postTitle)
                    ->first();
                if (! $post) {
                    continue;
                }

                if ($likerNames === null) {
                    $userIds = PostView::where('post_id', $post->id)->pluck('user_id')->unique()->all();
                } else {
                    $userIds = [];
                    foreach ($likerNames as $name) {
                        $user = User::where('name', $name)->first();
                        if ($user && $user->id !== $creator->id) {
                            $userIds[] = $user->id;
                        }
                    }
                }

                $postCreatedAt = Carbon::parse($post->created_at);
                $offsetHours = 2;
                foreach ($userIds as $userId) {
                    $likeAt = $postCreatedAt->copy()
                        ->addHours($offsetHours)
                        ->addMinutes(random_int(0, 59))
                        ->addSeconds(random_int(0, 59));

                    PostLike::firstOrCreate(
                        [
                            'post_id' => $post->id,
                            'user_id' => $userId,
                        ],
                        [
                            'created_at' => $likeAt,
                            'updated_at' => $likeAt,
                        ]
                    );
                    $offsetHours += 1 + random_int(0, 2);
                }
            }
        }
    }
}
