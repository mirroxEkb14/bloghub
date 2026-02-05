<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        $creatorProfileId = $this->user()?->creatorProfile?->id;
        return [
            'level' => [
                'required',
                'integer',
                Rule::in([1, 2, 3]),
                Rule::unique('tiers', 'level')
                    ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId)),
            ],
            'tier_name' => ['required', 'string', 'max:50'],
            'tier_desc' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1'],
            'currency' => ['required', new Enum(Currency::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => __('messages.tier_level_invalid'),
        ];
    }
}
