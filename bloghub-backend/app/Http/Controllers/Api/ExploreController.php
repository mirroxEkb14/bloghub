<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorProfileResource;
use App\Http\Resources\PostResource;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class ExploreController extends Controller
{
    private const POPULAR_CREATORS_LIMIT = 12;
    private const TRENDING_POSTS_LIMIT = 12;
    private const TRENDING_DAYS = 30;

    public function popularCreators(Request $request): AnonymousResourceCollection
    {
        $query = CreatorProfile::query()
            ->with(['user:id,name,username', 'tags'])
            ->withCount('posts')
            ->withCount(['subscriptions as subscriptions_count' => function ($q) {
                $q->where('sub_status', SubStatus::Active)
                    ->where(function ($q2) {
                        $q2->whereNull('end_date')->orWhere('end_date', '>', now());
                    });
            }])
            ->having('subscriptions_count', '>', 0)
            ->orderByDesc('subscriptions_count')
            ->limit(self::POPULAR_CREATORS_LIMIT);

        $profiles = $query->get();

        return CreatorProfileResource::collection($profiles);
    }

    public function trendingPosts(Request $request): AnonymousResourceCollection
    {
        $since = Carbon::now()->subDays(self::TRENDING_DAYS);

        $query = Post::query()
            ->with('creatorProfile:id,user_id,slug,display_name,profile_avatar_path')
            ->with('requiredTier:id,creator_profile_id,level,tier_name')
            ->withCount(['postViews as views_count' => fn ($q) => $q->where('post_views.created_at', '>=', $since)])
            ->withCount('comments')
            ->withCount('likes')
            ->orderByDesc('views_count')
            ->limit(self::TRENDING_POSTS_LIMIT);

        if ($request->user()) {
            $query->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $request->user()->id)])
                ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $request->user()->id)]);
        }

        $posts = $query->get();

        $user = $request->user();
        if ($user) {
            $creatorLevels = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
                ->with('tier:id,creator_profile_id,level')
                ->whereHas('tier')
                ->get()
                ->pluck('tier')
                ->filter()
                ->groupBy('creator_profile_id')
                ->map(fn ($tiers) => $tiers->max('level'))
                ->all();

            $isSuperAdmin = $user->hasRole('super_admin');

            $posts->each(function (Post $post) use ($user, $creatorLevels, $isSuperAdmin) {
                if ($post->required_tier_id === null) {
                    $post->user_has_access = true;
                    return;
                }
                if ($isSuperAdmin) {
                    $post->user_has_access = true;
                    return;
                }
                $isOwner = $post->relationLoaded('creatorProfile')
                    && $post->creatorProfile
                    && $post->creatorProfile->user_id === $user->id;
                if ($isOwner) {
                    $post->user_has_access = true;
                    return;
                }
                $profileId = $post->creator_profile_id;
                $userLevel = $creatorLevels[$profileId] ?? null;
                $requiredLevel = $post->requiredTier?->level ?? 0;
                $post->user_has_access = $userLevel !== null && $userLevel >= $requiredLevel;
            });
        } else {
            $posts->each(function (Post $post) {
                $post->user_has_access = $post->required_tier_id === null;
            });
        }

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', false);
        $request->attributes->set('creator_profile_user_tier_level', null);

        return PostResource::collection($posts);
    }
}
