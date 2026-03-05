<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostView;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PostViewSeeder extends Seeder
{
    private const VIEWER_NAME_MAP = [
        'Caroline' => 'Caroline',
        'Freeman' => 'Gordon Freeman',
        'Scully' => 'Dana Scully',
        'Mulder' => 'Fox Mulder',
        'Neo' => 'Thomas A. Anderson',
        'Trinity' => 'Tiffany Zion',
        'Ripley' => 'Ellen Ripley',
        'Maggie' => 'Maggie Rhee',
        'Negan' => 'Negan',
        'House' => 'Gregory House',
        'CJ' => 'Carl Johnson',
    ];

    private const PUBLIC_POST_VIEWS = [
        'Caroline' => [
            'The Borealis: A Lesson in Spontaneous Relocation' => ['Freeman', 'Scully', 'Mulder', 'Neo', 'Trinity'],
            'The Black Mesa Anomaly: A Study in Incompetence' => ['Ripley', 'Freeman', 'Maggie', 'Negan'],
        ],
        'Dana Scully' => [
            'Case File: 6x21 — The Brown Mountain Symbiosis' => ['Mulder', 'House', 'Maggie'],
            'Case File: 6x06 — The Holiday Solstice' => ['Mulder', 'House', 'Negan', 'CJ'],
        ],
        'Ellen Ripley' => [
            'XX121: The Predator Perfection' => ['Caroline', 'Scully', 'House', 'Negan'],
            'The Acheron (LV-426) Site' => ['Caroline', 'Scully', 'Mulder'],
        ],
        'Fox Mulder' => [
            'The Blackwood Anomaly and the Texas Bio-Lobby' => ['Caroline', 'Scully', 'Ripley', 'Freeman', 'House'],
        ],
        'Gordon Freeman' => [
            "The Universal 'Combine' Union" => ['Caroline', 'Scully', 'Ripley', 'Mulder', 'Neo', 'Trinity'],
            '"Xenocrystal Bloom"' => ['Caroline'],
            'Resonance Cascade Event – Video Insight' => ['Caroline', 'Scully', 'Ripley', 'Mulder'],
            'Resonance Cascade Event' => ['Caroline', 'Scully', 'Ripley', 'Mulder', 'House', 'Maggie', 'Negan', 'Neo', 'Trinity', 'CJ'],
        ],
        'Maggie Rhee' => [
            'From Survivor to Architect' => ['House', 'Negan'],
        ],
        'Negan' => [
            'A Retrospective on Staying Alive' => ['Caroline', 'Freeman', 'House', 'Maggie'],
        ],
    ];

    public function run(): void
    {
        $this->seedViewsForLockedPosts();
        $this->seedViewsForPublicPosts();
    }

    private function seedViewsForLockedPosts(): void
    {
        $lockedPosts = Post::with(['creatorProfile', 'requiredTier'])
            ->whereNotNull('required_tier_id')
            ->get();

        foreach ($lockedPosts as $post) {
            $requiredLevel = $post->requiredTier?->level;
            if ($requiredLevel === null) {
                continue;
            }

            $subs = Subscription::query()
                ->whereHas('tier', function ($q) use ($post, $requiredLevel) {
                    $q->where('creator_profile_id', $post->creator_profile_id)
                        ->where('level', '>=', $requiredLevel);
                })
                ->with('tier')
                ->get();

            foreach ($subs as $sub) {
                $viewAfter = Carbon::parse($post->created_at)->max($sub->start_date);
                $viewBefore = $sub->end_date;
                if ($viewAfter->gt($viewBefore)) {
                    continue;
                }
                $viewAt = $this->randomBetween($viewAfter, $viewBefore);

                PostView::firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $sub->user_id,
                    ],
                    [
                        'created_at' => $viewAt,
                        'updated_at' => $viewAt,
                    ]
                );
            }
        }
    }

    private function seedViewsForPublicPosts(): void
    {
        foreach (self::PUBLIC_POST_VIEWS as $creatorUserName => $titleToViewers) {
            $creator = User::where('name', $creatorUserName)->first();
            if (! $creator) {
                continue;
            }
            $profile = $creator->creatorProfile;
            if (! $profile) {
                continue;
            }

            foreach ($titleToViewers as $postTitle => $viewerShortNames) {
                $post = Post::where('creator_profile_id', $profile->id)
                    ->where('title', $postTitle)
                    ->first();
                if (! $post) {
                    continue;
                }

                $postCreatedAt = Carbon::parse($post->created_at);
                foreach ($viewerShortNames as $shortName) {
                    $viewerUserName = self::VIEWER_NAME_MAP[$shortName] ?? $shortName;
                    $viewer = User::where('name', $viewerUserName)->first();
                    if (! $viewer || $viewer->id === $creator->id) {
                        continue;
                    }

                    $viewAt = $this->randomBetween(
                        $postCreatedAt->copy(),
                        $postCreatedAt->copy()->addDays(30)
                    );

                    PostView::firstOrCreate(
                        [
                            'post_id' => $post->id,
                            'user_id' => $viewer->id,
                        ],
                        [
                            'created_at' => $viewAt,
                            'updated_at' => $viewAt,
                        ]
                    );
                }
            }
        }
    }

    private function randomBetween(Carbon $start, Carbon $end): Carbon
    {
        $startTs = $start->getTimestamp();
        $endTs = $end->getTimestamp();
        $ts = $startTs + (int) (($endTs - $startTs) * (mt_rand() / mt_getrandmax()));
        return Carbon::createFromTimestamp($ts);
    }
}
