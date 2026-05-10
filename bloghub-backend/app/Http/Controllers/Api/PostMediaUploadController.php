<?php

namespace App\Http\Controllers\Api;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadPostMediaRequest;
use App\Services\PublicDiskUploadService;
use App\Support\StorageUrlSupport;
use Illuminate\Http\JsonResponse;

class PostMediaUploadController extends Controller
{
    private const POST_MEDIA_DIRECTORY = 'posts/media';

    public function __construct(
        private PublicDiskUploadService $publicDiskUpload,
    ) {}

    public function upload(UploadPostMediaRequest $request): JsonResponse
    {
        $file = $request->file('media');
        $mime = $file->getMimeType();

        $mediaType = $this->mediaTypeFromMime($mime);
        if ($mediaType === null) {
            return response()->json(['message' => 'Unsupported file type'], 422);
        }

        $path = $this->publicDiskUpload->storeUploadedFile($file, self::POST_MEDIA_DIRECTORY, 'bin');
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
}
