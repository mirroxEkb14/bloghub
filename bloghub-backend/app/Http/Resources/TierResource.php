<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'tier_name' => $this->tier_name,
            'tier_desc' => $this->tier_desc,
            'price' => $this->price,
            'tier_currency' => $this->tier_currency?->value,
            'tier_cover_url' => $this->tier_cover_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
