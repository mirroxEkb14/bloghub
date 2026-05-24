<?php

namespace Tests\Unit\Policies;

use App\Models\Comment;
use App\Models\CreatorProfile;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\CreatorProfilePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PostPolicy;
use App\Policies\RolePolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\TierPolicy;
use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\Support\CreatesPostFixtures;
use Tests\Support\CreatesSubscriptionFixtures;
use Tests\Support\SeedsPermissions;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use CreatesPostFixtures;
    use CreatesSubscriptionFixtures;
    use RefreshDatabase;
    use SeedsPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_subscription_cancel_allows_owner(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $owner = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $owner->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);

        $this->assertTrue((new SubscriptionPolicy())->cancel($owner, $subscription));
    }

    public function test_subscription_cancel_denies_other_user(): void
    {
        ['tiers' => $tiers] = $this->createCreatorWithTiers();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $subscription = Subscription::query()->create([
            'user_id' => $owner->id,
            'tier_id' => $tiers[1]->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);

        $this->assertFalse((new SubscriptionPolicy())->cancel($other, $subscription));
    }

    public function test_super_admin_has_filament_post_abilities(): void
    {
        $user = $this->createSuperAdmin();
        $post = $this->makePost();
        $policy = new PostPolicy();

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->view($user, $post));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->update($user, $post));
        $this->assertTrue($policy->delete($user, $post));
    }

    public function test_user_without_permissions_denied_filament_post_abilities(): void
    {
        $user = $this->createUserWithoutPanelPermissions();
        $post = $this->makePost();
        $policy = new PostPolicy();

        $this->assertFalse($policy->viewAny($user));
        $this->assertFalse($policy->view($user, $post));
        $this->assertFalse($policy->create($user));
    }

    public function test_user_with_explicit_permission_can_view_post(): void
    {
        $user = $this->createUserWithoutPanelPermissions();
        $this->assignPermission($user, 'View:Post');
        $post = $this->makePost();

        $this->assertTrue((new PostPolicy())->view($user, $post));
        $this->assertFalse((new PostPolicy())->delete($user, $post));
    }

    public function test_super_admin_has_tier_and_subscription_panel_abilities(): void
    {
        $user = $this->createSuperAdmin();
        $tier = $this->makeTier();
        $subscription = $this->makeSubscription($tier);

        $this->assertTrue((new TierPolicy())->update($user, $tier));
        $this->assertTrue((new SubscriptionPolicy())->view($user, $subscription));
    }

    public function test_super_admin_can_manage_roles_admin_cannot(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createAdmin();
        $rolePolicy = new RolePolicy();

        $this->assertTrue($rolePolicy->viewAny($superAdmin));
        $this->assertFalse($rolePolicy->viewAny($admin));
    }

    public function test_admin_has_content_permissions_without_role_management(): void
    {
        $admin = $this->createAdmin();
        $post = $this->makePost();
        $tier = $this->makeTier();
        $profile = $tier->creatorProfile;
        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $admin->id,
            'content_text' => 'Admin comment',
        ]);
        $payment = Payment::query()->create([
            'subscription_id' => $this->makeSubscription($tier)->id,
            'amount' => 10,
            'currency' => 'USD',
            'card_last4' => '4242',
            'payment_status' => PaymentStatus::Completed,
        ]);

        $this->assertTrue((new PostPolicy())->view($admin, $post));
        $this->assertTrue((new TierPolicy())->view($admin, $tier));
        $this->assertTrue((new CreatorProfilePolicy())->view($admin, $profile));
        $this->assertTrue((new CommentPolicy())->view($admin, $comment));
        $this->assertTrue((new PaymentPolicy())->view($admin, $payment));
        $this->assertFalse((new RolePolicy())->viewAny($admin));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('modelPolicyProvider')]
    public function test_super_admin_can_delete_resource(string $policyClass, string $ability, string $modelMaker): void
    {
        $user = $this->createSuperAdmin();
        $model = $this->{$modelMaker}();
        $policy = new $policyClass();

        $this->assertTrue($policy->{$ability}($user, $model));
    }

    /**
     * @return array<string, array{0: class-string, 1: string, 2: string}>
     */
    public static function modelPolicyProvider(): array
    {
        return [
            'post' => [PostPolicy::class, 'delete', 'makePost'],
            'tier' => [TierPolicy::class, 'delete', 'makeTier'],
            'creator profile' => [CreatorProfilePolicy::class, 'delete', 'makeCreatorProfile'],
            'comment' => [CommentPolicy::class, 'delete', 'makeComment'],
            'payment' => [PaymentPolicy::class, 'delete', 'makePayment'],
        ];
    }

    private function makePost(): Post
    {
        ['profile' => $profile] = $this->createCreatorProfile();

        return $this->createPostForProfile($profile);
    }

    private function makeTier(): Tier
    {
        return $this->createCreatorWithTiers()['tiers'][1];
    }

    private function makeCreatorProfile(): CreatorProfile
    {
        return $this->createCreatorProfile()['profile'];
    }

    private function makeSubscription(?Tier $tier = null): Subscription
    {
        $tier = $tier ?? $this->makeTier();

        return Subscription::query()->create([
            'user_id' => User::factory()->create()->id,
            'tier_id' => $tier->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'sub_status' => SubStatus::Active,
        ]);
    }

    private function makeComment(): Comment
    {
        $post = $this->makePost();

        return Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'content_text' => 'Test comment',
        ]);
    }

    private function makePayment(): Payment
    {
        $subscription = $this->makeSubscription();

        return Payment::query()->create([
            'subscription_id' => $subscription->id,
            'amount' => 5,
            'currency' => 'USD',
            'card_last4' => '1111',
            'payment_status' => PaymentStatus::Completed,
        ]);
    }
}
