<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadTierCoverRequest;
use App\Support\StorageUrlSupport;
use App\Support\TierResourceSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TierCoverUploadController extends Controller
{
    public function cover(UploadTierCoverRequest $request): JsonResponse
    {
        $file = $request->file('cover');
        $path = $this->storeImage($file, TierResourceSupport::COVER_DIRECTORY);

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

    private function storeImage($file, string $directory): string|false
    {
        $disk = Storage::disk('public');

        $root = storage_path('app/public');
        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }
        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $name = Str::uuid().'.'.($extension ?? 'jpg');

        return $file->storeAs($directory, $name, ['disk' => 'public']);
    }
}
