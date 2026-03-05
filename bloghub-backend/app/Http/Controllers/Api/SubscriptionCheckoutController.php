<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConfirmCheckoutRequest;
use App\Http\Requests\Api\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\Tier;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;

class SubscriptionCheckoutController extends Controller
{
    public function createCheckoutSession(SubscribeRequest $request, StripePaymentService $stripePayment): JsonResponse
    {
        $user = $request->user();
        $tierId = (int) $request->input('tier_id');
        $tier = Tier::query()->with('creatorProfile')->findOrFail($tierId);

        if ($tier->price <= 0) {
            $startDate = now();
            $endDate = $startDate->copy()->addMonth();
            $subscription = Subscription::query()->create([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sub_status' => SubStatus::Active,
            ]);
            $subscription->load(['tier', 'tier.creatorProfile']);

            return response()->json([
                'type' => 'free',
                'subscription' => new SubscriptionResource($subscription),
            ]);
        }

        $creatorSlug = $tier->creatorProfile?->slug ?? '';
        $frontendUrl = config('services.frontend_url');
        $successUrl = $frontendUrl.'/creator/'.$creatorSlug.'?subscribe=success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $frontendUrl.'/creator/'.$creatorSlug.'?subscribe=cancel';

        $session = $stripePayment->createCheckoutSession($user, $tier, $successUrl, $cancelUrl);

        return response()->json([
            'type' => 'checkout',
            'checkout_url' => $session->url,
        ]);
    }

    public function confirmCheckout(ConfirmCheckoutRequest $request, StripePaymentService $stripePayment): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $result = $stripePayment->getCheckoutSessionStatus($request->input('session_id'), $userId);

        if ($result['status'] === 'active' && isset($result['subscription'])) {
            return response()->json([
                'status' => 'active',
                'subscription' => new SubscriptionResource($result['subscription']),
            ]);
        }

        return response()->json([
            'status' => $result['status'],
            'message' => match ($result['status']) {
                'webhook_unavailable' => __('Our payment provider\'s connection is temporarily unavailable D:'),
                'unpaid' => __('This checkout session was not paid'),
                default => __('Invalid or expired session'),
            },
        ], $result['status'] === 'active' ? 200 : 422);
    }
}
