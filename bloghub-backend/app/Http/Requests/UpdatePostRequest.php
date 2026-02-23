<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Models\Post;
use App\Support\PostResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            && $this->user()?->creatorProfile?->id === $post->creator_profile_id;
    }

    public function rules(): array
    {
        $post = $this->route('post');
        $creatorProfileId = (int) $this->user()?->creatorProfile?->id;

        $slugRules = [
            'sometimes',
            'required',
            'string',
            'max:'.PostResourceSupport::SLUG_MAX_LENGTH,
            ...PostResourceSupport::slugUniqueRules($creatorProfileId, $post?->id),
        ];

        return [
            'slug' => $slugRules,
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:'.PostResourceSupport::TITLE_MAX_LENGTH,
            ],
            'content_text' => [
                'sometimes',
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
