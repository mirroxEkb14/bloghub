<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreatorProfileUploadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_avatar_requires_authentication(): void
    {
        $this->post('/api/creator-profiles/upload-avatar', [], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_upload_avatar_stores_valid_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('avatar.png', 256, 'image/png');

        $res = $this->post('/api/creator-profiles/upload-avatar', ['avatar' => $file], ['Accept' => 'application/json']);

        $res->assertOk()
            ->assertJsonStructure(['path', 'url']);

        $path = (string) $res->json('path');
        $this->assertStringStartsWith('creator-profiles/avatars/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_avatar_rejects_invalid_file_type(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('avatar.gif', 256, 'image/gif');

        $this->post('/api/creator-profiles/upload-avatar', ['avatar' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_upload_cover_stores_valid_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('cover.jpg', 512, 'image/jpeg');

        $res = $this->post('/api/creator-profiles/upload-cover', ['cover' => $file], ['Accept' => 'application/json']);

        $res->assertOk()
            ->assertJsonStructure(['path', 'url']);

        $path = (string) $res->json('path');
        $this->assertStringStartsWith('creator-profiles/covers/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_cover_rejects_too_large_file(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('huge-cover.jpg', 6 * 1024, 'image/jpeg');

        $this->post('/api/creator-profiles/upload-cover', ['cover' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cover']);
    }
}
