<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends FormRequest
{
    private const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        $creatorProfileId = $this->user()?->creatorProfile?->id;
        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::regex(self::SLUG_REGEX),
                Rule::unique('posts', 'slug')
                    ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId)),
            ],
            'title' => ['required', 'string', 'max:50'],
            'content_text' => [
                'required',
                'string',
                'max:65535',
            ],
            'media_type' => ['nullable', new Enum(MediaType::class)],
            'media_url' => [
                'nullable',
                'string',
                'max:255',
                'url',
                Rule::requiredIf(fn () => $this->filled('media_type')),
            ],
            'required_tier_id' => [
                'nullable',
                'integer',
                Rule::exists('tiers', 'id')
                    ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => __('messages.slug_invalid'),
            'media_url.required' => __('messages.media_url_required'),
        ];
    }
}
