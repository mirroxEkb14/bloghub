<?php

namespace App\Http\Requests;

use App\Enums\SubStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $subscription = $this->route('subscription');
        return $subscription && $this->user() && $subscription->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'sub_status' => ['sometimes', 'required', new Enum(SubStatus::class)],
        ];
    }
}
