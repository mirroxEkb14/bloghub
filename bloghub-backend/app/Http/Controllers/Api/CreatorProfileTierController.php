<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TierResource;
use App\Models\CreatorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CreatorProfileTierController extends Controller
{
    public function index(string $slug): AnonymousResourceCollection|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found.')], 404);
        }

        $tiers = $profile->tiers()->orderBy('level')->get();

        return TierResource::collection($tiers);
    }
}
