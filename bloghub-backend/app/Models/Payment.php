<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Currency;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'checkout_date',
        'card_last4',
        'payment_status',
    ];

    protected $casts = [
        'checkout_date' => 'datetime',
        'currency' => Currency::class,
        'payment_status' => PaymentStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            if ($payment->checkout_date === null) {
                $payment->checkout_date = now();
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getSubscriptionLabelAttribute(): string
    {
        $subscription = $this->subscription;
        if (! $subscription) {
            return '–';
        }
        $parts = ['#'.$subscription->id];
        if ($subscription->user?->name) {
            $parts[] = $subscription->user->name;
        }
        if ($subscription->tier?->tier_name) {
            $parts[] = $subscription->tier->tier_name;
        }
        return implode(' · ', $parts);
    }
}
