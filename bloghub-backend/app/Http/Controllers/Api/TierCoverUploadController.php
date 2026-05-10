<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadTierCoverRequest;
use App\Services\PublicDiskUploadService;
use App\Support\StorageUrlSupport;
use App\Support\TierResourceSupport;
use Illuminate\Http\JsonResponse;

class TierCoverUploadController extends Controller
{
    public function __construct(
        private PublicDiskUploadService $publicDiskUpload,
    ) {}

    public function cover(UploadTierCoverRequest $request): JsonResponse
    {
        $file = $request->file('cover');
        $path = $this->publicDiskUpload->storeUploadedFile($file, TierResourceSupport::COVER_DIRECTORY);

        if ($path === false || $path === '') {
            return response()->json([
                'message' => 'Failed to store cover. Check storage permissions and that storage/app/public exists',
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => StorageUrlSupport::publicUrl($path),
        ]);
    }
}
