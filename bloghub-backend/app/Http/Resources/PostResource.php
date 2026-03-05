<?php

namespace App\Http\Resources;

use App\Support\StorageUrlSupport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mediaUrl = $this->media_url;
        if ($mediaUrl !== null && $mediaUrl !== '' && ! str_starts_with($mediaUrl, 'http')) {
            $mediaUrl = StorageUrlSupport::publicUrl($mediaUrl);
        }

        $userTierLevel = $request->attributes->get('creator_profile_user_tier_level');
        $userHasAccess = $this->required_tier_id === null
            || ($userTierLevel !== null && $this->requiredTier && $userTierLevel >= $this->requiredTier->level);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content_text' => $this->content_text,
            'excerpt' => $this->excerpt,
            'media_url' => $mediaUrl,
            'media_type' => $this->media_type?->value,
            'required_tier' => $this->whenLoaded('requiredTier', fn () => [
                'id' => $this->requiredTier->id,
                'level' => $this->requiredTier->level,
                'tier_name' => $this->requiredTier->tier_name,
            ]),
            'user_has_access' => $userHasAccess,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
