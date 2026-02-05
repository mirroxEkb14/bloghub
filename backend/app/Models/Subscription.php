<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SubStatus;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'tier_id',
        'start_date',
        'end_date',
        'sub_status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sub_status' => SubStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
