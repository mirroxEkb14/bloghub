<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesCreatorProfileFixtures;
use Tests\TestCase;

class TierCoverUploadApiTest extends TestCase
{
    use CreatesCreatorProfileFixtures;
    use RefreshDatabase;

    public function test_upload_cover_requires_authentication(): void
    {
        $this->post('/api/me/creator-profile/tiers/upload-cover', [], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_upload_cover_requires_creator_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_creator' => false]));

        $this->post('/api/me/creator-profile/tiers/upload-cover', [], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    public function test_upload_cover_stores_valid_image(): void
    {
        Storage::fake('public');
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('tier-cover.webp', 400, 'image/webp');

        $res = $this->post('/api/me/creator-profile/tiers/upload-cover', ['cover' => $file], ['Accept' => 'application/json']);

        $res->assertOk()
            ->assertJsonStructure(['path', 'url']);

        $path = (string) $res->json('path');
        $this->assertStringStartsWith('tiers/covers/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_cover_rejects_invalid_file_type(): void
    {
        Storage::fake('public');
        ['user' => $user] = $this->createCreatorProfile();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('cover.gif', 400, 'image/gif');

        $this->post('/api/me/creator-profile/tiers/upload-cover', ['cover' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cover']);
    }
}
