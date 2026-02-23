<?php

namespace App\Support;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\PaymentResource;
use App\Models\Payment;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentResourceSupport
{
    public const CARD_LAST4_MAX_LENGTH = 4;
    public const CURRENCY_MAX_LENGTH = 3;
    public const PAYMENT_STATUS_MAX_LENGTH = 20;

    private function __construct()
    {
    }

    public static function currencyOptions(): array
    {
        $options = [];
        foreach (Currency::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }

    public static function paymentStatusOptions(): array
    {
        $options = [];
        foreach (PaymentStatus::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }

    public static function recordViewUrl(Payment $record): string
    {
        return PaymentResource::getUrl('view', ['record' => $record]);
    }

    public static function tableModifyQueryUsing(): Closure
    {
        return static function (Builder $query): Builder {
            return $query->with(['subscription.user', 'subscription.tier']);
        };
    }

    public static function formatSubscriptionForTable(): Closure
    {
        return static function (mixed $state, ?Model $record): string {
            if (! $record instanceof Payment) {
                return '—';
            }
            $subscription = $record->subscription;
            if (! $subscription) {
                return '—';
            }
            $parts = ['#'.$subscription->id];
            if ($subscription->user?->name) {
                $parts[] = $subscription->user->name;
            }
            if ($subscription->tier?->tier_name) {
                $parts[] = $subscription->tier->tier_name;
            }
            return implode(' · ', $parts);
        };
    }

    public static function formatCurrencyForTable(): Closure
    {
        return static function (mixed $state): mixed {
            return $state?->value ?? $state;
        };
    }

    public static function formatPaymentStatusForTable(): Closure
    {
        return static function (mixed $state): mixed {
            return $state?->value ?? $state;
        };
    }
}
