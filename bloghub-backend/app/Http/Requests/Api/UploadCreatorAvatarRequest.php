<?php

namespace App\Http\Requests\Api;

use App\Support\CreatorProfileResourceSupport;
use Illuminate\Foundation\Http\FormRequest;

class UploadCreatorAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                'max:' . CreatorProfileResourceSupport::MAX_FILE_SIZE_KB,
            ],
        ];
    }
}
