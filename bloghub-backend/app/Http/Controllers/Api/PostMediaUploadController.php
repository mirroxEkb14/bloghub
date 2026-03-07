<?php

namespace App\Http\Controllers\Api;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadPostMediaRequest;
use App\Support\StorageUrlSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostMediaUploadController extends Controller
{
    private const POST_MEDIA_DIRECTORY = 'posts/media';

    public function upload(UploadPostMediaRequest $request): JsonResponse
    {
        $file = $request->file('media');
        $mime = $file->getMimeType();

        $mediaType = $this->mediaTypeFromMime($mime);
        if ($mediaType === null) {
            return response()->json(['message' => 'Unsupported file type'], 422);
        }

        $path = $this->storeFile($file);
        if ($path === false || $path === '') {
            return response()->json([
                'message' => 'Failed to store media. Check storage permissions',
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => StorageUrlSupport::publicUrl($path),
            'media_type' => $mediaType->value,
        ]);
    }

    private function mediaTypeFromMime(string $mime): ?MediaType
    {
        return match (true) {
            $mime === 'image/gif' => MediaType::Gif,
            str_starts_with($mime, 'image/') => MediaType::Image,
            str_starts_with($mime, 'video/') => MediaType::Video,
            str_starts_with($mime, 'audio/') => MediaType::Audio,
            default => null,
        };
    }

    private function storeFile($file): string|false
    {
        $disk = Storage::disk('public');
        $root = storage_path('app/public');
        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }
        if (! $disk->exists(self::POST_MEDIA_DIRECTORY)) {
            $disk->makeDirectory(self::POST_MEDIA_DIRECTORY);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $name = Str::uuid().'.'.($extension ?? 'bin');

        return $file->storeAs(self::POST_MEDIA_DIRECTORY, $name, ['disk' => 'public']);
    }
}
