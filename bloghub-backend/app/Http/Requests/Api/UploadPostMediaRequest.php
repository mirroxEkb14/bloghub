<?php

namespace App\Http\Requests\Api;

use App\Support\PostResourceSupport;
use Illuminate\Foundation\Http\FormRequest;

class UploadPostMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        $maxKb = max(PostResourceSupport::MEDIA_MAX_SIZE_KB);

        return [
            'media' => [
                'required',
                'file',
                'max:'.$maxKb,
                'mimes:jpeg,jpg,png,webp,gif,mp4,mp3,mpeg,m4a',
            ],
        ];
    }
}
