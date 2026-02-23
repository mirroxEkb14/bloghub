<?php

namespace App\Support;

use App\Enums\MediaType;
use Illuminate\Validation\Rule;

class PostResourceSupport
{
    public const SLUG_MAX_LENGTH = 255;
    public const TITLE_MAX_LENGTH = 50;
    public const CONTENT_TEXT_MIN_LENGTH = 1;
    public const CONTENT_TEXT_MAX_LENGTH = 65535;
    public const MEDIA_URL_MAX_LENGTH = 255;
    public const MEDIA_TYPE_MAX_LENGTH = 20;

    private function __construct()
    {
    }

    public static function slugUniqueRules(int $creatorProfileId, ?int $ignorePostId = null): array
    {
        $rule = Rule::unique('posts', 'slug')
            ->where('creator_profile_id', $creatorProfileId);

        if ($ignorePostId !== null) {
            $rule->ignore($ignorePostId);
        }

        return [$rule];
    }

    public static function requiredTierBelongsToCreatorRules(int $creatorProfileId): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('tiers', 'id')->where('creator_profile_id', $creatorProfileId),
        ];
    }

    public static function mediaTypeOptions(): array
    {
        $options = [];
        foreach (MediaType::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }
}
