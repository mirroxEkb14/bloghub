<?php

namespace Tests\Feature\Api;

use App\Models\CreatorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createCreatorUser(): array
    {
        $user = User::factory()->create([
            'username' => 'creator_user_' . fake()->unique()->userName(),
            'is_creator' => true,
        ]);

        $profile = CreatorProfile::query()->create([
            'user_id' => $user->id,
            'slug' => 'creator-' . fake()->unique()->slug(2),
            'display_name' => 'Creator ' . fake()->unique()->firstName(),
            'about' => null,
            'profile_avatar_path' => null,
            'profile_cover_path' => null,
            'telegram_url' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'youtube_url' => null,
            'twitch_url' => null,
            'website_url' => null,
        ]);

        return [$user, $profile];
    }

    public function test_upload_media_requires_authentication(): void
    {
        $this->post('/api/me/creator-profile/posts/upload-media', [], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_upload_media_requires_creator_profile(): void
    {
        $user = User::factory()->create(['is_creator' => false]);
        Sanctum::actingAs($user);

        $this->post('/api/me/creator-profile/posts/upload-media', [])
            ->assertForbidden();
    }

    public function test_upload_media_rejects_unsupported_file_type(): void
    {
        Storage::fake('public');

        [$user] = $this->createCreatorUser();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream');

        $this->post('/api/me/creator-profile/posts/upload-media', ['media' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['media']);
    }

    public function test_upload_media_rejects_too_large_file(): void
    {
        Storage::fake('public');

        [$user] = $this->createCreatorUser();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('big.mp4', 70 * 1024, 'video/mp4');

        $this->post('/api/me/creator-profile/posts/upload-media', ['media' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['media']);
    }

    public function test_upload_media_accepts_valid_image_and_stores_file(): void
    {
        Storage::fake('public');

        [$user] = $this->createCreatorUser();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('photo.png', 256, 'image/png');

        $res = $this->post('/api/me/creator-profile/posts/upload-media', ['media' => $file], ['Accept' => 'application/json']);

        $res->assertOk()
            ->assertJsonStructure(['path', 'url', 'media_type'])
            ->assertJsonPath('media_type', 'Image');

        $path = (string) $res->json('path');
        $this->assertNotSame('', $path);
        $this->assertStringStartsWith('posts/media/', $path);
        Storage::disk('public')->assertExists($path);
    }
}

