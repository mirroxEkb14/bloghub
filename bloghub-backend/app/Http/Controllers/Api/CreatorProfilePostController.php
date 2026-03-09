<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\CreatorProfile;
use App\Models\PostLike;
use App\Models\PostView;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CreatorProfilePostController extends Controller
{
    public function index(Request $request, string $slug): AnonymousResourceCollection|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $userTierLevel = null;
        $user = $request->user();
        $isProfileOwner = $user && $user->id === $profile->user_id;
        $isSuperAdmin = $user && $user->hasRole(UserRoleEnum::SuperAdmin->value);
        $hasFullAccess = $isProfileOwner || $isSuperAdmin;

        if ($user && ! $hasFullAccess) {
            $subscription = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where('end_date', '>', now())
                ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $profile->id))
                ->with('tier:id,level')
                ->first();
            $userTierLevel = $subscription?->tier?->level;
        } elseif ($hasFullAccess) {
            $userTierLevel = PHP_INT_MAX;
        }
        $request->attributes->set('creator_profile_user_tier_level', $userTierLevel);
        $request->attributes->set('creator_profile_is_owner', $isProfileOwner ?? false);
        $request->attributes->set('creator_profile_is_super_admin', $isSuperAdmin ?? false);

        $query = $profile->posts()
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->with('requiredTier:id,creator_profile_id,level,tier_name')
            ->orderByDesc('created_at');

        if ($user) {
            $query->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $user->id)])
                ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $user->id)]);
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        return PostResource::collection($posts);
    }

    public function show(Request $request, string $slug, string $postSlug): PostResource|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $post = $profile->posts()
            ->where('slug', $postSlug)
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->with('requiredTier:id,creator_profile_id,level,tier_name')
            ->first();

        if ($post && $request->user()) {
            $post->loadCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $request->user()->id)])
                ->loadCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $request->user()->id)]);
        }

        if ($post === null) {
            return response()->json(['message' => __('Post not found')], 404);
        }

        $user = $request->user();
        $isProfileOwner = $user && $user->id === $profile->user_id;
        $isSuperAdmin = $user && $user->hasRole(UserRoleEnum::SuperAdmin->value);
        $hasFullAccess = $isProfileOwner || $isSuperAdmin;

        if ($hasFullAccess) {
            $request->attributes->set('creator_profile_user_tier_level', PHP_INT_MAX);
            $request->attributes->set('creator_profile_is_owner', $isProfileOwner);
            $request->attributes->set('creator_profile_is_super_admin', $isSuperAdmin);
        } else {
            $request->attributes->set('creator_profile_is_owner', false);
            $request->attributes->set('creator_profile_is_super_admin', false);
            $userTierLevel = null;
            if ($user) {
                $subscription = Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('sub_status', SubStatus::Active)
                    ->where('end_date', '>', now())
                    ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $profile->id))
                    ->with('tier:id,level')
                    ->first();
                $userTierLevel = $subscription?->tier?->level;
            }
            $request->attributes->set('creator_profile_user_tier_level', $userTierLevel);
        }

        if ($post->required_tier_id !== null && ! $hasFullAccess) {
            if (! $user) {
                return response()->json([
                    'message' => __('This post is for subscribers only'),
                    'requires_subscription' => true,
                    'required_tier' => [
                        'id' => $post->requiredTier->id,
                        'tier_name' => $post->requiredTier->tier_name,
                        'level' => $post->requiredTier->level,
                    ],
                ], 403);
            }
            $hasAccess = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where('end_date', '>', now())
                ->whereHas('tier', function ($q) use ($post) {
                    $q->where('creator_profile_id', $post->creator_profile_id)
                        ->where('level', '>=', $post->requiredTier->level);
                })
                ->exists();
            if (! $hasAccess) {
                return response()->json([
                    'message' => __('This post is for subscribers only'),
                    'requires_subscription' => true,
                    'required_tier' => [
                        'id' => $post->requiredTier->id,
                        'tier_name' => $post->requiredTier->tier_name,
                        'level' => $post->requiredTier->level,
                    ],
                ], 403);
            }
        }

        return new PostResource($post);
    }

    public function recordView(Request $request, string $slug, string $postSlug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $post = $profile->posts()->where('slug', $postSlug)->first();

        if ($post === null) {
            return response()->json(['message' => __('Post not found')], 404);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => __('Unauthenticated')], 401);
        }

        PostView::firstOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]
        );

        return response()->json(['message' => 'OK'], 204);
    }

    public function like(Request $request, string $slug, string $postSlug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $post = $profile->posts()->where('slug', $postSlug)->first();

        if ($post === null) {
            return response()->json(['message' => __('Post not found')], 404);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => __('Unauthenticated')], 401);
        }

        PostLike::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'OK'], 204);
    }

    public function unlike(Request $request, string $slug, string $postSlug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $post = $profile->posts()->where('slug', $postSlug)->first();

        if ($post === null) {
            return response()->json(['message' => __('Post not found')], 404);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => __('Unauthenticated')], 401);
        }

        PostLike::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'OK'], 204);
    }
}
