<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePostRequest extends FormRequest
{
    private const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        $post = $this->route('post');
        return $post && $this->user()?->creatorProfile?->id === $post->creator_profile_id;
    }

    public function rules(): array
    {
        $post = $this->route('post');
        $creatorProfileId = $this->user()?->creatorProfile?->id;

        $slugUnique = Rule::unique('posts', 'slug')
            ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId));
        if ($post) {
            $slugUnique->ignore($post->id);
        }

        return [
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::regex(self::SLUG_REGEX),
                $slugUnique,
            ],
            'title' => ['sometimes', 'required', 'string', 'max:50'],
            'content_text' => ['sometimes', 'required', 'string', 'max:65535'],
            'media_type' => [
                'nullable',
                new Enum(MediaType::class),
                Rule::requiredIf(fn () => $this->filled('media_url'))
            ],
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
