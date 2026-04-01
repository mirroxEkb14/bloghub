<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTierRequest;
use App\Http\Requests\Api\UpdateTierRequest;
use App\Http\Resources\TierResource;
use App\Models\CreatorProfile;
use App\Models\Tier;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CreatorProfileTierController extends Controller
{
    private const MAX_TIERS = 3;

    public function index(string $slug): AnonymousResourceCollection|JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $tiers = $profile->tiers()->orderBy('level')->get();

        return TierResource::collection($tiers);
    }

    public function indexMy(): AnonymousResourceCollection|JsonResponse
    {
        $profile = request()->user()?->creatorProfile;
        if ($profile === null) {
            return response()->json(['message' => __('You do not have a creator profile')], 404);
        }
        $tiers = $profile->tiers()->orderBy('level')->get();

        return TierResource::collection($tiers);
    }

    public function store(StoreTierRequest $request, NotificationService $notifications): JsonResponse
    {
        $profile = $request->user()->creatorProfile;
        if ($profile->tiers()->count() >= self::MAX_TIERS) {
            return response()->json([
                'message' => __('You can have at most :max tiers', ['max' => self::MAX_TIERS]),
            ], 422);
        }
        $usedLevels = $profile->tiers()->pluck('level')->all();
        $nextLevel = collect([1, 2, 3])->first(fn (int $l) => ! in_array($l, $usedLevels, true));
        if ($nextLevel === null) {
            return response()->json(['message' => __('No available tier level')], 422);
        }
        $tier = $profile->tiers()->create([
            'level' => $nextLevel,
            'tier_name' => $request->input('tier_name'),
            'tier_desc' => $request->input('tier_desc'),
            'price' => (int) $request->input('price'),
            'tier_currency' => $request->input('tier_currency'),
            'tier_cover_path' => $request->input('tier_cover_path'),
        ]);

        $tier->load('creatorProfile');
        $notifications->tierCreated($tier);

        return response()->json(new TierResource($tier), 201);
    }

    public function update(UpdateTierRequest $request, Tier $tier, NotificationService $notifications): TierResource
    {
        $data = $request->validated();
        $tier->update($data);
        $tier->load('creatorProfile');
        $notifications->tierEdited($tier->fresh());

        return new TierResource($tier->fresh());
    }

    public function destroy(Tier $tier, NotificationService $notifications): JsonResponse|Response
    {
        $profile = request()->user()?->creatorProfile;
        if ($profile === null || $tier->creator_profile_id !== $profile->id) {
            return response()->json(['message' => __('Tier not found or access denied')], 404);
        }
        $tierId = $tier->id;
        $tierName = $tier->tier_name;
        $creatorProfile = $tier->creatorProfile;
        $tier->delete();

        $notifications->tierRemoved($tierId, $tierName, $creatorProfile);

        return response()->noContent();
    }
}
