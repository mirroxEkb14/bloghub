<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Support\TierResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tier = $this->route('tier');

        return $tier && $this->user()?->creatorProfile?->id === $tier->creator_profile_id;
    }

    public function rules(): array
    {
        $tier = $this->route('tier');
        $creatorProfileId = $this->user()?->creatorProfile?->id;

        $levelUnique = Rule::unique('tiers', 'level')
            ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId));
        if ($tier) {
            $levelUnique->ignore($tier->id);
        }

        return [
            'level' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(TierResourceSupport::LEVEL_VALUES),
                $levelUnique,
            ],
            'tier_name' => ['sometimes', 'required', 'string', 'max:'.TierResourceSupport::NAME_MAX_LENGTH],
            'tier_desc' => ['sometimes', 'required', 'string', 'max:'.TierResourceSupport::DESC_MAX_LENGTH],
            'price' => ['sometimes', 'required', 'integer', 'min:1'],
            'tier_currency' => ['sometimes', 'required', new Enum(Currency::class)],
            'tier_cover_path' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:'.TierResourceSupport::COVER_MAX_FILE_SIZE_KB,
                'dimensions:max_width='.TierResourceSupport::COVER_MAX_WIDTH.',max_height='.TierResourceSupport::COVER_MAX_HEIGHT,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => __('messages.tier_level_invalid'),
        ];
    }
}
