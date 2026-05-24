<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private function createNotification(User $user, string $type = 'new_post', ?array $data = null, ?\DateTimeInterface $readAt = null): Notification
    {
        return Notification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data ?? ['post_title' => 'Test notification'],
            'read_at' => $readAt,
        ]);
    }

    public function test_notification_endpoints_require_authentication(): void
    {
        $notification = Notification::query()->create([
            'user_id' => User::factory()->create()->id,
            'type' => 'new_post',
            'data' => [],
        ]);

        $this->getJson('/api/me/notifications')->assertUnauthorized();
        $this->getJson('/api/me/notifications/unread-count')->assertUnauthorized();
        $this->patchJson('/api/me/notifications/read')->assertUnauthorized();
        $this->patchJson("/api/me/notifications/{$notification->id}/read")->assertUnauthorized();
    }

    public function test_index_returns_paginated_notifications_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user, 'subscription_canceled', [
            'tier_name' => 'Gold',
        ]);
        Sanctum::actingAs($user);

        $res = $this->getJson('/api/me/notifications');

        $res->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'type',
                    'data',
                    'read_at',
                    'created_at',
                ]],
                'links',
                'meta',
            ])
            ->assertJsonPath('data.0.id', $notification->id)
            ->assertJsonPath('data.0.type', 'subscription_canceled')
            ->assertJsonPath('data.0.data.tier_name', 'Gold')
            ->assertJsonPath('data.0.read_at', null);
    }

    public function test_index_excludes_other_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createNotification($user, 'new_post', ['post_title' => 'Mine']);
        $this->createNotification($other, 'new_post', ['post_title' => 'Theirs']);
        Sanctum::actingAs($user);

        $res = $this->getJson('/api/me/notifications');

        $res->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Mine', $res->json('data.0.data.post_title'));
    }

    public function test_index_orders_notifications_by_newest_first(): void
    {
        $user = User::factory()->create();
        $older = $this->createNotification($user);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();
        $newer = $this->createNotification($user);
        $newer->forceFill(['created_at' => now()->subDay()])->save();
        Sanctum::actingAs($user);

        $ids = collect($this->getJson('/api/me/notifications')->json('data'))->pluck('id')->all();

        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_unread_count_returns_number_of_unread_notifications(): void
    {
        $user = User::factory()->create();
        $this->createNotification($user);
        $this->createNotification($user);
        $read = $this->createNotification($user);
        $read->update(['read_at' => now()]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('count', 2);
    }

    public function test_unread_count_returns_zero_when_all_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user);
        $notification->update(['read_at' => now()]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('count', 0);
    }

    public function test_mark_read_marks_single_notification(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user);
        Sanctum::actingAs($user);

        $res = $this->patchJson("/api/me/notifications/{$notification->id}/read");

        $res->assertOk()
            ->assertJsonPath('id', $notification->id)
            ->assertJsonPath('type', 'new_post');

        $this->assertNotNull($res->json('read_at'));
        $this->assertNotNull($notification->fresh()->read_at);

        $this->getJson('/api/me/notifications/unread-count')
            ->assertJsonPath('count', 0);
    }

    public function test_mark_read_forbidden_for_other_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = $this->createNotification($owner);
        Sanctum::actingAs($other);

        $this->patchJson("/api/me/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read_marks_all_unread_notifications(): void
    {
        $user = User::factory()->create();
        $this->createNotification($user);
        $this->createNotification($user);
        $alreadyRead = $this->createNotification($user, 'new_post', null, now());
        Sanctum::actingAs($user);

        $this->patchJson('/api/me/notifications/read')
            ->assertOk()
            ->assertJsonPath('message', 'All notifications marked as read');

        $this->assertSame(0, Notification::query()->where('user_id', $user->id)->whereNull('read_at')->count());
        $this->assertNotNull($alreadyRead->fresh()->read_at);

        $this->getJson('/api/me/notifications/unread-count')
            ->assertJsonPath('count', 0);
    }
}
