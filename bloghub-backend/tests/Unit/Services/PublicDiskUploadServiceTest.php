<?php

namespace Tests\Unit\Services;

use App\Services\PublicDiskUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicDiskUploadServiceTest extends TestCase
{
    public function test_store_uploaded_file_saves_under_directory_with_uuid_name(): void
    {
        Storage::fake('public');
        $service = new PublicDiskUploadService();
        $file = UploadedFile::fake()->create('photo.png', 128, 'image/png');

        $path = $service->storeUploadedFile($file, 'posts/media');

        $this->assertIsString($path);
        $this->assertStringStartsWith('posts/media/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_uploaded_file_uses_fallback_extension_when_missing(): void
    {
        Storage::fake('public');
        $service = new PublicDiskUploadService();
        $file = UploadedFile::fake()->create('noextension', 64, 'application/octet-stream');

        $path = $service->storeUploadedFile($file, 'uploads', 'webp');

        $this->assertIsString($path);
        $this->assertStringStartsWith('uploads/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_stored_file_is_publicly_addressable(): void
    {
        Storage::fake('public');
        $service = new PublicDiskUploadService();
        $file = UploadedFile::fake()->create('avatar.jpg', 64, 'image/jpeg');

        $path = $service->storeUploadedFile($file, 'creators/avatars');

        $this->assertNotFalse($path);
        $url = Storage::disk('public')->url($path);
        $this->assertStringContainsString('creators/avatars/', $url);
    }
}
