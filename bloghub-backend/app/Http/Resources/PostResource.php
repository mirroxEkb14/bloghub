<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content_text' => $this->content_text,
            'media_url' => $this->media_url,
            'media_type' => $this->media_type?->value,
            'required_tier' => $this->whenLoaded('requiredTier', fn () => [
                'id' => $this->requiredTier->id,
                'level' => $this->requiredTier->level,
                'tier_name' => $this->requiredTier->tier_name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
