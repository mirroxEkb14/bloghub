<?php

namespace App\Http\Requests;

use App\Enums\Currency;
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
                Rule::in([1, 2, 3]),
                $levelUnique,
            ],
            'tier_name' => ['sometimes', 'required', 'string', 'max:50'],
            'tier_desc' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'integer', 'min:1'],
            'currency' => ['sometimes', 'required', new Enum(Currency::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => __('messages.tier_level_invalid'),
        ];
    }
}
