<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

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
        } else {
            $subscribedProfileIds = Subscription::query()
                ->where('user_id', $user->id)
                ->whereIn('sub_status', [SubStatus::Active, SubStatus::Canceled])
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

            $followedProfileIds = $user->followingCreatorProfiles()->get()->pluck('id')->all();
            $profileIds = array_values(array_unique(array_merge($subscribedProfileIds, $followedProfileIds)));

            if (count($profileIds) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
            } else {
                $query = Post::query()
                    ->whereIn('creator_profile_id', $profileIds)
                    ->whereNull('required_tier_id');
            }
        }

        $posts = $this->paginateFeedQuery($query, $request, $user, eagerRequiredTier: false);

        $this->setFeedRequestResourceContext($request, $user, unlimitedTierForResource: false);

        return PostResource::collection($posts);
    }

    public function tierFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query()->whereNotNull('required_tier_id');
            $creatorLevels = [];
        } else {
            [$creatorLevels, $followedProfileIds] = $this->subscribedTierLevelsAndFollowedProfileIds($user);
            $subscribedProfileIds = array_keys($creatorLevels);

            if (count($subscribedProfileIds) === 0 && count($followedProfileIds) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
            } else {
                $query = Post::query()
                    ->whereNotNull('required_tier_id')
                    ->where(function ($q) use ($subscribedProfileIds, $creatorLevels, $followedProfileIds) {
                        if (count($subscribedProfileIds) > 0) {
                            $q->where(function ($q2) use ($subscribedProfileIds, $creatorLevels) {
                                $q2->whereIn('creator_profile_id', $subscribedProfileIds)
                                    ->whereHas('requiredTier', function ($q3) use ($creatorLevels) {
                                        $q3->where(function ($q4) use ($creatorLevels) {
                                            foreach ($creatorLevels as $profileId => $level) {
                                                $q4->orWhere(fn ($q5) => $q5->where('creator_profile_id', $profileId)->where('level', '<=', $level));
                                            }
                                        });
                                    });
                            });
                        }
                        if (count($followedProfileIds) > 0) {
                            $q->orWhere(function ($q2) use ($followedProfileIds) {
                                $q2->whereIn('creator_profile_id', $followedProfileIds);
                            });
                        }
                    });
            }
        }

        $posts = $this->paginateFeedQuery($query, $request, $user, eagerRequiredTier: true);

        $this->applyUserHasAccessToFeedPosts(
            $posts->getCollection(),
            $user,
            $creatorLevels,
            grantAccessWhenNoTierRequired: false,
        );

        $this->setFeedRequestResourceContext($request, $user, unlimitedTierForResource: true);

        return PostResource::collection($posts);
    }

    public function homeFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query();
            $creatorLevels = [];
        } else {
            [$creatorLevels, $followedProfileIds] = $this->subscribedTierLevelsAndFollowedProfileIds($user);
            $subscribedProfileIds = array_keys($creatorLevels);
            $subscribedOrFollowedIds = array_values(array_unique(array_merge($subscribedProfileIds, $followedProfileIds)));
            $profileIds = array_keys($creatorLevels);

            if (count($subscribedOrFollowedIds) === 0 && count($profileIds) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
            } else {
                $query = Post::query()->where(function ($q) use ($subscribedOrFollowedIds, $profileIds, $creatorLevels, $followedProfileIds) {
                    if (count($subscribedOrFollowedIds) > 0) {
                        $q->where(function ($q2) use ($subscribedOrFollowedIds) {
                            $q2->whereIn('creator_profile_id', $subscribedOrFollowedIds)
                                ->whereNull('required_tier_id');
                        });
                    }
                    if (count($profileIds) > 0) {
                        $q->orWhere(function ($q2) use ($profileIds, $creatorLevels) {
                            $q2->whereNotNull('required_tier_id')
                                ->whereIn('creator_profile_id', $profileIds)
                                ->whereHas('requiredTier', function ($q3) use ($creatorLevels) {
                                    $q3->where(function ($q4) use ($creatorLevels) {
                                        foreach ($creatorLevels as $creatorProfileId => $level) {
                                            $q4->orWhere(fn ($q5) => $q5->where('creator_profile_id', $creatorProfileId)->where('level', '<=', $level));
                                        }
                                    });
                                });
                        });
                    }
                    if (count($followedProfileIds) > 0) {
                        $q->orWhere(function ($q2) use ($followedProfileIds) {
                            $q2->whereNotNull('required_tier_id')
                                ->whereIn('creator_profile_id', $followedProfileIds);
                        });
                    }
                });
            }
        }

        $posts = $this->paginateFeedQuery($query, $request, $user, eagerRequiredTier: true);

        $this->applyUserHasAccessToFeedPosts(
            $posts->getCollection(),
            $user,
            $creatorLevels,
            grantAccessWhenNoTierRequired: true,
        );

        $this->setFeedRequestResourceContext($request, $user, unlimitedTierForResource: true);

        return PostResource::collection($posts);
    }

    private function paginateFeedQuery(Builder $query, Request $request, User $user, bool $eagerRequiredTier): LengthAwarePaginator
    {
        $this->applyCreatorSearch($query, $request);

        $query->with('creatorProfile:id,slug,display_name,profile_avatar_path');
        if ($eagerRequiredTier) {
            $query->with('requiredTier:id,creator_profile_id,level,tier_name');
        }
        $query
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('created_at');

        $perPage = min((int) $request->input('per_page', 15), 50);

        return $query->paginate($perPage);
    }

    private function setFeedRequestResourceContext(Request $request, User $user, bool $unlimitedTierForResource): void
    {
        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $user->hasRole('super_admin'));
        $request->attributes->set('creator_profile_user_tier_level', $unlimitedTierForResource ? PHP_INT_MAX : null);
    }

    private function applyUserHasAccessToFeedPosts(
        Collection $posts,
        User $user,
        array $creatorLevels,
        bool $grantAccessWhenNoTierRequired,
    ): void {
        $isSuperAdmin = $user->hasRole('super_admin');
        $userCreatorProfileId = $user->creatorProfile?->id;

        foreach ($posts as $post) {
            if ($grantAccessWhenNoTierRequired && $post->required_tier_id === null) {
                $post->user_has_access = true;

                continue;
            }
            if ($isSuperAdmin) {
                $post->user_has_access = true;

                continue;
            }
            if ($userCreatorProfileId !== null && $post->creator_profile_id === $userCreatorProfileId) {
                $post->user_has_access = true;

                continue;
            }
            $userLevel = $creatorLevels[$post->creator_profile_id] ?? null;
            $requiredLevel = $post->requiredTier?->level ?? 0;
            $post->user_has_access = $userLevel !== null && $userLevel >= $requiredLevel;
        }
    }

    private function subscribedTierLevelsAndFollowedProfileIds(User $user): array
    {
        $subscriptions = Subscription::query()
            ->where('user_id', $user->id)
            ->whereIn('sub_status', [SubStatus::Active, SubStatus::Canceled])
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

        $followedProfileIds = $user->followingCreatorProfiles()->get()->pluck('id')->all();

        return [$creatorLevels, $followedProfileIds];
    }
}
