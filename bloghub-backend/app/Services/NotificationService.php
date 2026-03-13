<?php

namespace App\Services;

use App\Enums\SubStatus;
use App\Models\CreatorProfile;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\Tier;

class NotificationService
{
    public function subscriptionCanceled(Subscription $subscription, bool $endNow): void
    {
        $subscription->load(['tier', 'tier.creatorProfile']);
        $tier = $subscription->tier;
        $creator = $tier?->creatorProfile;

        Notification::query()->create([
            'user_id' => $subscription->user_id,
            'type' => 'subscription_canceled',
            'data' => [
                'subscription_id' => $subscription->id,
                'end_now' => $endNow,
                'end_date' => $subscription->end_date?->toIso8601String(),
                'tier_name' => $tier?->tier_name,
                'creator_slug' => $creator?->slug,
                'creator_display_name' => $creator?->display_name,
                'creator_avatar_url' => $creator?->profile_avatar_url,
            ],
        ]);
    }

    public function subscriptionExpired(Subscription $subscription): void
    {
        $subscription->load(['tier', 'tier.creatorProfile']);
        $tier = $subscription->tier;
        $creator = $tier?->creatorProfile;

        Notification::query()->create([
            'user_id' => $subscription->user_id,
            'type' => 'subscription_expired',
            'data' => [
                'subscription_id' => $subscription->id,
                'tier_name' => $tier?->tier_name,
                'creator_slug' => $creator?->slug,
                'creator_display_name' => $creator?->display_name,
                'creator_avatar_url' => $creator?->profile_avatar_url,
            ],
        ]);
    }

    public function newPost(Post $post): void
    {
        $profile = $post->creatorProfile;
        if (! $profile) {
            return;
        }

        $userIds = $this->followerAndSubscriberUserIds($profile);

        $data = [
            'post_id' => $post->id,
            'post_slug' => $post->slug,
            'post_title' => $post->title,
            'creator_slug' => $profile->slug,
            'creator_display_name' => $profile->display_name,
            'creator_avatar_url' => $profile->profile_avatar_url,
        ];

        foreach ($userIds as $userId) {
            Notification::query()->create([
                'user_id' => $userId,
                'type' => 'new_post',
                'data' => $data,
            ]);
        }
    }

    public function tierCreated(Tier $tier): void
    {
        $this->tierChange($tier, 'tier_created');
    }

    public function tierEdited(Tier $tier): void
    {
        $this->tierChange($tier, 'tier_edited');
    }

    public function tierRemoved(int $tierId, string $tierName, ?CreatorProfile $profile): void
    {
        if (! $profile) {
            return;
        }

        $userIds = $this->followerAndSubscriberUserIds($profile);

        $data = [
            'tier_id' => $tierId,
            'tier_name' => $tierName,
            'creator_slug' => $profile->slug,
            'creator_display_name' => $profile->display_name,
            'creator_avatar_url' => $profile->profile_avatar_url,
        ];

        foreach ($userIds as $userId) {
            Notification::query()->create([
                'user_id' => $userId,
                'type' => 'tier_removed',
                'data' => $data,
            ]);
        }
    }

    private function tierChange(Tier $tier, string $type): void
    {
        $profile = $tier->creatorProfile;
        if (! $profile) {
            return;
        }

        $userIds = $this->followerAndSubscriberUserIds($profile);

        $data = [
            'tier_id' => $tier->id,
            'tier_name' => $tier->tier_name,
            'creator_slug' => $profile->slug,
            'creator_display_name' => $profile->display_name,
            'creator_avatar_url' => $profile->profile_avatar_url,
        ];

        foreach ($userIds as $userId) {
            Notification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'data' => $data,
            ]);
        }
    }

    public function processExpiredSubscriptions(): int
    {
        $expired = Subscription::query()
            ->whereIn('sub_status', [SubStatus::Active, SubStatus::Canceled])
            ->where('end_date', '<', now())
            ->with(['tier', 'tier.creatorProfile'])
            ->get();

        $count = 0;
        foreach ($expired as $subscription) {
            $alreadyNotified = Notification::query()
                ->where('type', 'subscription_expired')
                ->where('user_id', $subscription->user_id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.subscription_id')) = ?", [(string) $subscription->id])
                ->exists();

            if (! $alreadyNotified) {
                $this->subscriptionExpired($subscription);
                $count++;
            }
        }

        return $count;
    }

    private function followerAndSubscriberUserIds(CreatorProfile $profile): array
    {
        $followerIds = $profile->followers()->pluck('users.id')->all();

        $subscriberIds = Subscription::query()
            ->whereIn('sub_status', [SubStatus::Active, SubStatus::Canceled])
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $profile->id))
            ->pluck('user_id')
            ->all();

        return array_values(array_unique(array_merge($followerIds, $subscriberIds)));
    }
}
