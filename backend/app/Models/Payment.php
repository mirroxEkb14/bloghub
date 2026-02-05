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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
