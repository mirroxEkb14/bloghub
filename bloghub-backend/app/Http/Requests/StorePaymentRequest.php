<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'subscription_id' => [
                'required',
                'integer',
                Rule::exists('subscriptions', 'id'),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', new Enum(Currency::class)],
            'card_last4' => [
                'required',
                'string',
                'regex:/^\d{4}$/',
            ],
            'payment_status' => ['sometimes', new Enum(PaymentStatus::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->subscriptionBelongsToUser()) {
                $validator->errors()->add(
                    'subscription_id',
                    __('messages.subscription_not_owned')
                );
            }
        });
    }

    private function subscriptionBelongsToUser(): bool
    {
        $subscriptionId = $this->input('subscription_id');

        if (! $subscriptionId) {
            return true;
        }

        return Subscription::query()
            ->where('id', $subscriptionId)
            ->where('user_id', $this->user()->id)
            ->exists();
    }
}
