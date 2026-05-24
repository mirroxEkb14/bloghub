<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Test User',
            'username' => 'test_user',
            'email' => 'test.user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_accepted' => true,
            'privacy_accepted' => true,
        ];

        $res = $this->postJson('/api/register', $payload);

        $res->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'user' => ['id', 'name', 'username', 'email'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'username' => $payload['username'],
        ]);

        $user = User::where('email', $payload['email'])->firstOrFail();
        $this->assertTrue(Hash::check($payload['password'], $user->password));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $password = 'Password123!';
        $user = User::factory()->create([
            'email' => 'login.user@example.com',
            'password' => Hash::make($password),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $res->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'user' => ['id', 'name', 'username', 'email'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_endpoint_requires_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_login_rejects_wrong_password(): void
    {
        $password = 'Password123!';
        $user = User::factory()->create([
            'email' => 'wrong.password@example.com',
            'password' => Hash::make($password),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'WrongPassword123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        Notification::fake();

        $existing = User::factory()->create(['email' => 'duplicate@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Another User',
            'username' => 'another_user',
            'email' => $existing->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_accepted' => true,
            'privacy_accepted' => true,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_missing_terms_acceptance(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'username' => 'terms_user',
            'email' => 'terms.user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_accepted' => false,
            'privacy_accepted' => true,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_register_rejects_weak_password(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'username' => 'weak_pass_user',
            'email' => 'weak.pass@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms_accepted' => true,
            'privacy_accepted' => true,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('auth');
        $plainTextToken = $tokenResult->plainTextToken;
        $accessTokenId = $tokenResult->accessToken->id;

        $this->withToken($plainTextToken)
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $accessTokenId,
        ]);

        $this->refreshApplication();

        $this->withToken($plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_update_profile_updates_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'old_username',
            'email' => 'old.name@example.com',
            'phone' => null,
        ]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/user', [
            'name' => 'New Name',
            'username' => 'new_username',
            'email' => 'new.name@example.com',
            'phone' => '420 111 222 333',
        ])
            ->assertOk()
            ->assertJsonPath('user.name', 'New Name')
            ->assertJsonPath('user.username', 'new_username')
            ->assertJsonPath('user.email', 'new.name@example.com')
            ->assertJsonPath('user.phone', '+420111222333');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'username' => 'new_username',
            'email' => 'new.name@example.com',
            'phone' => '+420111222333',
        ]);
    }

    public function test_resend_verification_email_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/email/resend')
            ->assertOk()
            ->assertJsonPath('message', 'Verification link sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}

