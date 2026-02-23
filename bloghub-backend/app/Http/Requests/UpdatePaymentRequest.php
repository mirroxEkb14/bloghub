<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        if (! $payment instanceof Payment || ! $this->user()) {
            return false;
        }

        return Subscription::query()
            ->where('id', $payment->subscription_id)
            ->where('user_id', $this->user()->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'payment_status' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                new Enum(PaymentStatus::class),
            ],
        ];
    }
}
