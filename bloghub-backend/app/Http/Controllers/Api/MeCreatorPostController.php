<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
