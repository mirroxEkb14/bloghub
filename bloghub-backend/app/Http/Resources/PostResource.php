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

        $isProfileOwner = (bool) $request->attributes->get('creator_profile_is_owner', false);
        $isSuperAdmin = (bool) $request->attributes->get('creator_profile_is_super_admin', false);
        $userTierLevel = $request->attributes->get('creator_profile_user_tier_level');
        $userHasAccess = $isProfileOwner
            || $isSuperAdmin
            || $this->required_tier_id === null
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
            'views_count' => $this->when(isset($this->views_count), fn () => (int) $this->views_count),
            'user_has_viewed' => $this->when(isset($this->user_has_viewed), fn () => (bool) $this->user_has_viewed),
            'comments_count' => $this->when(isset($this->comments_count), fn () => (int) $this->comments_count),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
