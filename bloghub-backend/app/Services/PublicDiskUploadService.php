<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicDiskUploadService
{
    public function storeUploadedFile(UploadedFile $file, string $directory, string $fallbackExtension = 'jpg'): string|false
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
        $name = Str::uuid().'.'.($extension ?? $fallbackExtension);

        return $file->storeAs($directory, $name, ['disk' => 'public']);
    }
}
