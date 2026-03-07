<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency?->value,
            'checkout_date' => $this->checkout_date?->toIso8601String(),
            'card_last4' => $this->card_last4,
            'payment_status' => $this->payment_status?->value,
            'subscription' => $this->whenLoaded('subscription', function () {
                $sub = $this->subscription;
                $sub->loadMissing(['tier', 'tier.creatorProfile']);
                $tier = $sub->tier;
                $creator = $tier?->creatorProfile;
                return [
                    'id' => $sub->id,
                    'tier_name' => $tier?->tier_name,
                    'creator' => $creator ? [
                        'slug' => $creator->slug,
                        'display_name' => $creator->display_name,
                    ] : null,
                ];
            }),
        ];
    }
}
