<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\CreatorProfile;
use App\Models\Subscription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    use AuthorizesRequests;

    public function store(SubscribeRequest $request): SubscriptionResource|JsonResponse
    {
        $user = $request->user();
        $tierId = (int) $request->input('tier_id');

        $startDate = now();
        $endDate = $startDate->copy()->addMonth();

        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'tier_id' => $tierId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sub_status' => SubStatus::Active,
        ]);

        $subscription->load(['tier', 'tier.creatorProfile']);

        return new SubscriptionResource($subscription);
    }

    public function index(): AnonymousResourceCollection
    {
        $subscriptions = request()->user()
            ->subscriptions()
            ->with(['tier', 'tier.creatorProfile'])
            ->orderByDesc('created_at')
            ->get();

        return SubscriptionResource::collection($subscriptions);
    }

    public function statusByCreator(string $slug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found.')], 404);
        }

        $user = request()->user();
        if (! $user) {
            return response()->json([
                'subscribed' => false,
                'active_subscription' => null,
            ]);
        }

        $active = Subscription::query()
            ->where('user_id', $user->id)
            ->where('sub_status', SubStatus::Active)
            ->where('end_date', '>', now())
            ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $profile->id))
            ->with(['tier', 'tier.creatorProfile'])
            ->first();

        return response()->json([
            'subscribed' => $active !== null,
            'active_subscription' => $active ? (new SubscriptionResource($active))->toArray(request()) : null,
        ]);
    }

    public function cancel(Subscription $subscription): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        $subscription->update([
            'sub_status' => SubStatus::Canceled,
            'end_date' => now(),
        ]);

        return response()->json([
            'message' => __('Subscription canceled.'),
            'subscription' => new SubscriptionResource($subscription->load(['tier', 'tier.creatorProfile'])),
        ]);
    }
}
