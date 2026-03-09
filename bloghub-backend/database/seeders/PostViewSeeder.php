<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PostViewSeeder extends Seeder
{
    private const PUBLIC_POST_VIEWS = [
        'Caroline' => [
            'The Borealis: A Lesson in Spontaneous Relocation' => ['Gordon Freeman', 'Dana Scully', 'Fox Mulder', 'Thomas A. Anderson', 'Tiffany Zion'],
            'The Black Mesa Anomaly: A Study in Incompetence' => ['Ellen Ripley', 'Gordon Freeman', 'Maggie Rhee', 'Negan'],
        ],
        'Dana Scully' => [
            'Case File: 6x21 — The Brown Mountain Symbiosis' => ['Fox Mulder', 'Gregory House', 'Maggie Rhee'],
            'Case File: 6x06 — The Holiday Solstice' => ['Fox Mulder', 'Gregory House', 'Negan', 'Carl Johnson'],
        ],
        'Ellen Ripley' => [
            'XX121: The Predator Perfection' => ['Caroline', 'Dana Scully', 'Gregory House', 'Negan'],
            'The Acheron (LV-426) Site' => ['Caroline', 'Dana Scully', 'Fox Mulder'],
        ],
        'Fox Mulder' => [
            'The Blackwood Anomaly and the Texas Bio-Lobby' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Gordon Freeman', 'Gregory House'],
        ],
        'Gordon Freeman' => [
            "The Universal 'Combine' Union" => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Fox Mulder', 'Thomas A. Anderson', 'Tiffany Zion'],
            '"Xenocrystal Bloom" - Sound Insight' => ['Caroline'],
            'Resonance Cascade Event – Video Insight' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Fox Mulder'],
            'Resonance Cascade Event' => ['Caroline', 'Dana Scully', 'Ellen Ripley', 'Fox Mulder', 'Gregory House', 'Maggie Rhee', 'Negan', 'Thomas A. Anderson', 'Tiffany Zion', 'Carl Johnson'],
        ],
        'Maggie Rhee' => [
            'From Survivor to Architect' => ['Gregory House', 'Negan'],
        ],
        'Negan' => [
            'A Retrospective on Staying Alive' => ['Caroline', 'Gordon Freeman', 'Gregory House', 'Maggie Rhee'],
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
                ->whereHas('payments', fn ($q) => $q->where('payment_status', PaymentStatus::Completed))
                ->with('tier')
                ->get();

            $postCreatedAt = Carbon::parse($post->created_at);

            foreach ($subs as $sub) {
                $viewAfter = $postCreatedAt->copy()->max($sub->start_date);
                $viewBefore = $sub->end_date;

                if ($viewAfter->lte($viewBefore)) {
                    $viewAt = $this->randomBetween($viewAfter, $viewBefore);
                } else {
                    $viewAt = $postCreatedAt->copy()->addSecond();
                }

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

            foreach ($titleToViewers as $postTitle => $viewerNames) {
                $post = Post::where('creator_profile_id', $profile->id)
                    ->where('title', $postTitle)
                    ->first();
                if (! $post) {
                    continue;
                }

                $postCreatedAt = Carbon::parse($post->created_at);
                foreach ($viewerNames as $viewerName) {
                    $viewer = User::where('name', $viewerName)->first();
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
