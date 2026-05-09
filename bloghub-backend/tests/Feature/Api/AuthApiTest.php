<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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
}

