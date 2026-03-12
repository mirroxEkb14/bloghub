<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'about' => $this->about,
            'profile_avatar_url' => $this->profile_avatar_url,
            'profile_cover_url' => $this->profile_cover_url,
            'telegram_url' => $this->telegram_url,
            'instagram_url' => $this->instagram_url,
            'facebook_url' => $this->facebook_url,
            'youtube_url' => $this->youtube_url,
            'twitch_url' => $this->twitch_url,
            'website_url' => $this->website_url,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
            ]),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'posts_count' => $this->when(isset($this->posts_count), fn () => $this->posts_count),
            'followers_count' => $this->when(isset($this->followers_count), fn () => (int) $this->followers_count),
            'subscribers_count' => $this->when(isset($this->subscribers_count), fn () => (int) $this->subscribers_count),
            'subscriptions_count' => $this->when(isset($this->subscriptions_count), fn () => (int) $this->subscriptions_count),
            'is_following' => $this->when(isset($this->is_following), fn () => (bool) $this->is_following),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
