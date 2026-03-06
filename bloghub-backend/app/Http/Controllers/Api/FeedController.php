<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    private function applyCreatorSearch($query, Request $request): void
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return;
        }
        $term = '%'.$q.'%';
        $query->where(function ($builder) use ($term) {
            $builder->whereHas('creatorProfile', function ($profileBuilder) use ($term) {
                $profileBuilder->where('display_name', 'like', $term)
                    ->orWhereHas('user', function ($userBuilder) use ($term) {
                        $userBuilder->where('username', 'like', $term);
                    });
            })->orWhere('title', 'like', $term);
        });
    }

    public function publicFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query()->whereNull('required_tier_id');
            $subscribedProfileIds = null;
        } else {
            $subscribedProfileIds = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
                ->with('tier:id,creator_profile_id')
                ->whereHas('tier')
                ->get()
                ->pluck('tier.creator_profile_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($subscribedProfileIds) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
            } else {
                $query = Post::query()
                    ->whereIn('creator_profile_id', $subscribedProfileIds)
                    ->whereNull('required_tier_id');
            }
        }

        $this->applyCreatorSearch($query, $request);

        $query
            ->with('creatorProfile:id,slug,display_name,profile_avatar_path')
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('created_at');

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $user->hasRole('super_admin'));
        $request->attributes->set('creator_profile_user_tier_level', null);

        return PostResource::collection($posts);
    }

    public function tierFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query()->whereNotNull('required_tier_id');
        } else {
            $subscriptions = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
                ->with('tier:id,creator_profile_id,level')
                ->whereHas('tier')
                ->get();

            $creatorLevels = $subscriptions
                ->pluck('tier')
                ->filter()
                ->groupBy('creator_profile_id')
                ->map(fn ($tiers) => $tiers->max('level'))
                ->all();

            if (count($creatorLevels) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
                $profileIds = [];
            } else {
                $profileIds = array_keys($creatorLevels);
                $query = Post::query()
                    ->whereNotNull('required_tier_id')
                    ->whereIn('creator_profile_id', $profileIds)
                    ->whereHas('requiredTier', function ($q) use ($creatorLevels) {
                        $q->where(function ($q2) use ($creatorLevels) {
                            foreach ($creatorLevels as $profileId => $level) {
                                $q2->orWhere(fn ($q3) => $q3->where('creator_profile_id', $profileId)->where('level', '<=', $level));
                            }
                        });
                    });
            }
        }

        $this->applyCreatorSearch($query, $request);

        $query
            ->with('creatorProfile:id,slug,display_name,profile_avatar_path')
            ->with('requiredTier:id,creator_profile_id,level,tier_name')
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('created_at');

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $user->hasRole('super_admin'));
        $request->attributes->set('creator_profile_user_tier_level', PHP_INT_MAX);

        return PostResource::collection($posts);
    }
}
