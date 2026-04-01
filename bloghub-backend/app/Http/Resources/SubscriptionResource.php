<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tier_id' => $this->tier_id,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'sub_status' => $this->sub_status?->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'tier' => $this->whenLoaded('tier', fn () => new TierResource($this->tier)),
            'creator' => $this->whenLoaded('tier', function () {
                $profile = $this->tier->creatorProfile;
                if (! $profile) {
                    return null;
                }
                return [
                    'id' => $profile->id,
                    'slug' => $profile->slug,
                    'display_name' => $profile->display_name,
                    'profile_avatar_url' => $profile->profile_avatar_url,
                    'followers_count' => isset($profile->followers_count) ? (int) $profile->followers_count : null,
                    'last_post_at' => isset($profile->posts_max_created_at) && $profile->posts_max_created_at
                        ? Carbon::parse($profile->posts_max_created_at)->toIso8601String()
                        : null,
                ];
            }),
            'card_last4' => $this->getAttribute('card_last4'),
        ];
    }
}
