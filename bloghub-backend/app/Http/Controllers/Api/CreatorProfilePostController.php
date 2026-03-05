<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\CreatorProfile;
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

        $query = $profile->posts()
            ->withCount('comments')
            ->with('requiredTier:id,creator_profile_id,level,tier_name')
            ->orderByDesc('created_at');

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

        $post = $profile->posts()->where('slug', $postSlug)->with('requiredTier:id,creator_profile_id,level,tier_name')->first();

        if ($post === null) {
            return response()->json(['message' => __('Post not found')], 404);
        }

        if ($post->required_tier_id !== null) {
            $user = $request->user();
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
}
