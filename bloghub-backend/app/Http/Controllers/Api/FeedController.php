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
            $creatorLevels = [];
            $followedProfileIds = [];
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

            $followedProfileIds = $user->followingCreatorProfiles()->get()->pluck('id')->all();
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

        $isSuperAdmin = $user->hasRole('super_admin');
        $userCreatorProfileId = $user->creatorProfile?->id;

        $posts->getCollection()->each(function (Post $post) use ($user, $isSuperAdmin, $userCreatorProfileId, $creatorLevels) {
            if ($isSuperAdmin) {
                $post->user_has_access = true;
                return;
            }
            if ($userCreatorProfileId !== null && $post->creator_profile_id === $userCreatorProfileId) {
                $post->user_has_access = true;
                return;
            }
            $userLevel = $creatorLevels[$post->creator_profile_id] ?? null;
            $requiredLevel = $post->requiredTier?->level ?? 0;
            $post->user_has_access = $userLevel !== null && $userLevel >= $requiredLevel;
        });

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $isSuperAdmin);
        $request->attributes->set('creator_profile_user_tier_level', PHP_INT_MAX);

        return PostResource::collection($posts);
    }

    public function homeFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query();
            $subscribedProfileIds = [];
            $creatorLevels = [];
            $followedProfileIds = [];
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

            $followedProfileIds = $user->followingCreatorProfiles()->get()->pluck('id')->all();
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

        $isSuperAdmin = $user->hasRole('super_admin');
        $userCreatorProfileId = $user->creatorProfile?->id;

        $posts->getCollection()->each(function (Post $post) use ($user, $isSuperAdmin, $userCreatorProfileId, $creatorLevels, $followedProfileIds) {
            if ($post->required_tier_id === null) {
                $post->user_has_access = true;
                return;
            }
            if ($isSuperAdmin) {
                $post->user_has_access = true;
                return;
            }
            if ($userCreatorProfileId !== null && $post->creator_profile_id === $userCreatorProfileId) {
                $post->user_has_access = true;
                return;
            }
            $userLevel = $creatorLevels[$post->creator_profile_id] ?? null;
            $requiredLevel = $post->requiredTier?->level ?? 0;
            $post->user_has_access = $userLevel !== null && $userLevel >= $requiredLevel;
        });

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $isSuperAdmin);
        $request->attributes->set('creator_profile_user_tier_level', PHP_INT_MAX);

        return PostResource::collection($posts);
    }
}
