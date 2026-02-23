<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Support\PostResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        $creatorProfileId = (int) $this->user()?->creatorProfile?->id;

        return [
            'slug' => [
                'required',
                'string',
                'max:'.PostResourceSupport::SLUG_MAX_LENGTH,
                ...PostResourceSupport::slugUniqueRules($creatorProfileId),
            ],
            'title' => [
                'required',
                'string',
                'max:'.PostResourceSupport::TITLE_MAX_LENGTH,
            ],
            'content_text' => [
                'required',
                'string',
                'min:'.PostResourceSupport::CONTENT_TEXT_MIN_LENGTH,
                'max:'.PostResourceSupport::CONTENT_TEXT_MAX_LENGTH,
            ],
            'media_url' => [
                'nullable',
                'string',
                'max:'.PostResourceSupport::MEDIA_URL_MAX_LENGTH,
            ],
            'media_type' => [
                'nullable',
                'string',
                'max:'.PostResourceSupport::MEDIA_TYPE_MAX_LENGTH,
                new Enum(MediaType::class),
            ],
            'required_tier_id' => PostResourceSupport::requiredTierBelongsToCreatorRules($creatorProfileId),
        ];
    }
}
