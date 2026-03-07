<?php

namespace App\Http\Requests\Api;

use App\Support\TierResourceSupport;
use Illuminate\Foundation\Http\FormRequest;

class UploadTierCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        return [
            'cover' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                'max:'.TierResourceSupport::COVER_MAX_FILE_SIZE_KB,
            ],
        ];
    }
}
