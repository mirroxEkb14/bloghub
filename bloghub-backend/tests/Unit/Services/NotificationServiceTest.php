<?php

namespace Tests\Unit\Services;

use App\Enums\SubStatus;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function test_subscription_canceled_creates_notification(): void
    {
        ['creator' => $creator, 'profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $subscriber = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $subscriber->id,
            'tier_id' => $tiers[2]->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addDays(5),
            'sub_status' => SubStatus::Active,
        ]);

        $this->service->subscriptionCanceled($subscription, false);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $subscriber->id,
            'type' => 'subscription_canceled',
        ]);

        $notification = Notification::query()->where('user_id', $subscriber->id)->firstOrFail();
        $this->assertFalse($notification->data['end_now']);
        $this->assertSame($profile->slug, $notification->data['creator_slug']);
        $this->assertSame($tiers[2]->tier_name, $notification->data['tier_name']);
        $this->assertNotSame($creator->id, $subscriber->id);
    }

    public function test_new_post_notifies_followers_and_subscribers(): void
    {
        ['creator' => $creator, 'profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $follower = User::factory()->create();
        $subscriber = User::factory()->create();
        $profile->followers()->attach($follower->id);
        Subscription::query()->create([
            'user_id' => $subscriber->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);

        $post = $this->createPostForProfile($profile, [
            'slug' => 'notify-post',
            'title' => 'New content',
        ]);
        $post->load('creatorProfile');

        $this->service->newPost($post);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $follower->id,
            'type' => 'new_post',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $subscriber->id,
            'type' => 'new_post',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $creator->id,
            'type' => 'new_post',
        ]);

        $followerNotification = Notification::query()->where('user_id', $follower->id)->firstOrFail();
        $this->assertSame('notify-post', $followerNotification->data['post_slug']);
        $this->assertSame('New content', $followerNotification->data['post_title']);
    }

    public function test_tier_created_notifies_followers(): void
    {
        ['profile' => $profile, 'tiers' => $tiers] = $this->createCreatorWithTiers();
        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);
        $tier = $tiers[2];
        $tier->load('creatorProfile');

        $this->service->tierCreated($tier);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $follower->id,
            'type' => 'tier_created',
        ]);
    }

    public function test_creator_profile_removed_notifies_audience_but_not_owner(): void
    {
        ['user' => $creator, 'profile' => $profile] = $this->createCreatorProfile();
        $follower = User::factory()->create();
        $profile->followers()->attach($follower->id);

        $this->service->creatorProfileRemoved($profile);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $follower->id,
            'type' => 'creator_profile_removed',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $creator->id,
            'type' => 'creator_profile_removed',
        ]);
    }

    public function test_process_expired_subscriptions_creates_notification_once(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $subscriber = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $subscriber->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subDay(),
            'sub_status' => SubStatus::Active,
        ]);

        $count = $this->service->processExpiredSubscriptions();
        $this->assertSame(1, $count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $subscriber->id,
            'type' => 'subscription_expired',
        ]);

        $this->assertSame(0, $this->service->processExpiredSubscriptions());
    }
}
