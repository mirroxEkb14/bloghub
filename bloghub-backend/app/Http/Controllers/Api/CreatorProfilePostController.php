<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\CreatorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CreatorProfilePostController extends Controller
{
    public function index(Request $request, string $slug): AnonymousResourceCollection|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found.')], 404);
        }

        $query = $profile->posts()->with('requiredTier:id,creator_profile_id,level,tier_name')->orderByDesc('created_at');

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        return PostResource::collection($posts);
    }

    public function show(string $slug, string $postSlug): PostResource|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found.')], 404);
        }

        $post = $profile->posts()->where('slug', $postSlug)->with('requiredTier:id,creator_profile_id,level,tier_name')->first();

        if ($post === null) {
            return response()->json(['message' => __('Post not found.')], 404);
        }

        return new PostResource($post);
    }
}
