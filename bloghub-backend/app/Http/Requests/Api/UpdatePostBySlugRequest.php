<?php

namespace App\Http\Requests\Api;

use App\Enums\MediaType;
use App\Models\Post;
use App\Support\PostResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePostBySlugRequest extends FormRequest
{
    protected ?Post $post = null;

    public function authorize(): bool
    {
        $slug = $this->route('postSlug');
        $profile = $this->user()?->creatorProfile;
        if (! $profile || ! is_string($slug)) {
            return false;
        }
        $this->post = Post::query()
            ->where('creator_profile_id', $profile->id)
            ->where('slug', $slug)
            ->first();

        return $this->post !== null;
    }

    public function rules(): array
    {
        $creatorProfileId = (int) $this->user()?->creatorProfile?->id;
        $postId = $this->post?->id;

        $slugRules = [
            'sometimes',
            'required',
            'string',
            'max:'.PostResourceSupport::SLUG_MAX_LENGTH,
            ...PostResourceSupport::slugUniqueRules($creatorProfileId, $postId),
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
            'excerpt' => [
                'nullable',
                'string',
                'max:'.PostResourceSupport::EXCERPT_MAX_LENGTH,
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

    public function getPost(): Post
    {
        return $this->post;
    }
}
