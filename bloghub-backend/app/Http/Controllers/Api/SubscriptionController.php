<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\TierResource;
use App\Models\CreatorProfile;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $subscriptionIds = $subscriptions->pluck('id')->all();
        if (count($subscriptionIds) > 0) {
            $latestPaymentBySub = Payment::query()
                ->whereIn('subscription_id', $subscriptionIds)
                ->orderByDesc('checkout_date')
                ->get()
                ->unique('subscription_id')
                ->keyBy('subscription_id');
            $subscriptions->each(function (Subscription $sub) use ($latestPaymentBySub) {
                $payment = $latestPaymentBySub->get($sub->id);
                $sub->setAttribute('card_last4', $payment?->card_last4);
            });
        }

        return SubscriptionResource::collection($subscriptions);
    }

    public function statusByCreator(string $slug): JsonResponse
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $user = request()->user();
        if (! $user) {
            return response()->json([
                'subscribed' => false,
                'active_subscription' => null,
            ]);
        }

        if ($user->hasRole('super_admin')) {
            $topTier = $profile->tiers()->orderByDesc('level')->first();
            $activeSubscription = null;
            if ($topTier) {
                $topTier->load('creatorProfile');
                $activeSubscription = [
                    'id' => 0,
                    'user_id' => $user->id,
                    'tier_id' => $topTier->id,
                    'start_date' => null,
                    'end_date' => null,
                    'sub_status' => 'active',
                    'created_at' => null,
                    'updated_at' => null,
                    'tier' => (new TierResource($topTier))->toArray(request()),
                    'creator' => [
                        'id' => $profile->id,
                        'slug' => $profile->slug,
                        'display_name' => $profile->display_name,
                        'profile_avatar_url' => $profile->profile_avatar_url,
                    ],
                ];
            }
            return response()->json([
                'subscribed' => $topTier !== null,
                'active_subscription' => $activeSubscription,
            ]);
        }

        $active = Subscription::query()
            ->where('user_id', $user->id)
            ->whereIn('sub_status', [SubStatus::Active, SubStatus::Canceled])
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $profile->id))
            ->with(['tier', 'tier.creatorProfile'])
            ->join('tiers', 'subscriptions.tier_id', '=', 'tiers.id')
            ->orderByDesc('tiers.level')
            ->select('subscriptions.*')
            ->first();

        return response()->json([
            'subscribed' => $active !== null,
            'active_subscription' => $active ? (new SubscriptionResource($active))->toArray(request()) : null,
        ]);
    }

    public function cancel(Request $request, Subscription $subscription, NotificationService $notifications): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        $endNow = $request->boolean('end_now', false);

        $subscription->update([
            'sub_status' => SubStatus::Canceled,
            'end_date' => $endNow ? now() : $subscription->end_date,
        ]);

        $notifications->subscriptionCanceled($subscription->fresh(), $endNow);

        return response()->json([
            'message' => __('Subscription canceled'),
            'subscription' => new SubscriptionResource($subscription->load(['tier', 'tier.creatorProfile'])),
        ]);
    }
}
