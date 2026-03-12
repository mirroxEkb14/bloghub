<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePostBySlugRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class MeCreatorPostController extends Controller
{
    public function store(StorePostRequest $request): JsonResponse
    {
        $profile = $request->user()->creatorProfile;
        $data = $request->validated();
        $data['creator_profile_id'] = $profile->id;

        $post = Post::create($data);
        $post->load('requiredTier');

        $request->attributes->set('creator_profile_is_owner', true);
        $request->attributes->set('creator_profile_user_tier_level', PHP_INT_MAX);

        return response()->json(new PostResource($post), 201);
    }

    public function update(UpdatePostBySlugRequest $request, string $postSlug): JsonResponse
    {
        $post = $request->getPost();
        $post->update($request->validated());
        $post->load('requiredTier');

        return response()->json(new PostResource($post));
    }

    public function destroy(string $postSlug): JsonResponse
    {
        $profile = request()->user()->creatorProfile;
        if (! $profile) {
            return response()->json(['message' => 'Creator profile required'], 403);
        }

        $post = Post::query()
            ->where('creator_profile_id', $profile->id)
            ->where('slug', $postSlug)
            ->first();

        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();
        return response()->json(null, 204);
    }
}
