<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorProfileFollowController extends Controller
{
    public function follow(Request $request, string $slug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $user = $request->user();
        if ($user->id === $profile->user_id) {
            return response()->json(['message' => __('You cannot follow your own profile')], 422);
        }

        $user->followingCreatorProfiles()->syncWithoutDetaching([$profile->id]);

        return response()->json(['message' => __('Following')], 200);
    }

    public function unfollow(Request $request, string $slug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $request->user()->followingCreatorProfiles()->detach($profile->id);

        return response()->json(['message' => __('Unfollowed')], 200);
    }
}
