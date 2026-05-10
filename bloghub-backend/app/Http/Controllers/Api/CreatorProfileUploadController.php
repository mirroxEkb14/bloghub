<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadCreatorAvatarRequest;
use App\Http\Requests\Api\UploadCreatorCoverRequest;
use App\Services\PublicDiskUploadService;
use App\Support\CreatorProfileResourceSupport;
use App\Support\StorageUrlSupport;
use Illuminate\Http\JsonResponse;

class CreatorProfileUploadController extends Controller
{
    public function __construct(
        private PublicDiskUploadService $publicDiskUpload,
    ) {}

    public function avatar(UploadCreatorAvatarRequest $request): JsonResponse
    {
        $file = $request->file('avatar');
        $path = $this->publicDiskUpload->storeUploadedFile($file, CreatorProfileResourceSupport::AVATAR_DIRECTORY);

        if ($path === false || $path === '') {
            return response()->json([
                'message' => 'Failed to store avatar. Check storage permissions and that storage/app/public exists',
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => StorageUrlSupport::publicUrl($path),
        ]);
    }

    public function cover(UploadCreatorCoverRequest $request): JsonResponse
    {
        $file = $request->file('cover');
        $path = $this->publicDiskUpload->storeUploadedFile($file, CreatorProfileResourceSupport::COVER_DIRECTORY);

        if ($path === false || $path === '') {
            return response()->json([
                'message' => 'Failed to store cover image. Check storage permissions and that storage/app/public exists',
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => StorageUrlSupport::publicUrl($path),
        ]);
    }
}
