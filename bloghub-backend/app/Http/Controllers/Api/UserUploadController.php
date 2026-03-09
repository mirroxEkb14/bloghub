<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadUserAvatarRequest;
use App\Support\StorageUrlSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserUploadController extends Controller
{
    private const AVATAR_DIRECTORY = 'users/avatars';

    public function avatar(UploadUserAvatarRequest $request): JsonResponse
    {
        $file = $request->file('avatar');
        $path = $this->storeImage($file);

        if ($path === false || $path === '') {
            return response()->json([
                'message' => 'Failed to store avatar. Check storage permissions and that storage/app/public exists',
            ], 500);
        }

        $request->user()->update(['avatar_path' => $path]);

        return response()->json([
            'path' => $path,
            'url' => StorageUrlSupport::publicUrl($path),
        ]);
    }

    private function storeImage($file): string|false
    {
        $disk = Storage::disk('public');

        $root = storage_path('app/public');
        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }
        if (! $disk->exists(self::AVATAR_DIRECTORY)) {
            $disk->makeDirectory(self::AVATAR_DIRECTORY);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $name = Str::uuid() . '.' . ($extension ?? 'jpg');

        return $file->storeAs(self::AVATAR_DIRECTORY, $name, ['disk' => 'public']);
    }
}
