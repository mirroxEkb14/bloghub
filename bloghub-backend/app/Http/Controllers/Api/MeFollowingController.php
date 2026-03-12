<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeFollowingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $follows = $user->followingCreatorProfiles()
            ->with(['user:id,name,username', 'tags'])
            ->withCount(['posts', 'followers'])
            ->orderByDesc('creator_profile_follows.created_at')
            ->get();

        $data = $follows->map(function ($profile) {
            return [
                'creator_profile' => new CreatorProfileResource($profile),
                'followed_at' => $profile->pivot->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['data' => $data->values()->all()]);
    }
}
