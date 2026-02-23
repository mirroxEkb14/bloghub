<?php

namespace App\Http\Requests;

use App\Enums\SubStatus;
use App\Models\Tier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'tier_id' => [
                'required',
                'integer',
                Rule::exists('tiers', 'id'),
            ],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['required', 'date'],
            'sub_status' => [
                'required',
                'string',
                'max:20',
                new Enum(SubStatus::class),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->tierBelongsToCreator()) {
                $validator->errors()->add(
                    'tier_id',
                    __('messages.tier_invalid_creator')
                );
            }
        });
    }

    private function tierBelongsToCreator(): bool
    {
        $tierId = $this->input('tier_id');

        if (! $tierId) {
            return true;
        }

        return Tier::query()
            ->where('id', $tierId)
            ->whereHas('creatorProfile')
            ->exists();
    }
}
