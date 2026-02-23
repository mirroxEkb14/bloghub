<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CreatorProfileTag extends Pivot
{
    public $incrementing = false;

    protected $table = 'creator_profile_tag';

    protected $fillable = [
        'attached_at',
    ];

    protected $casts = [
        'attached_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CreatorProfileTag $pivot): void {
            if ($pivot->attached_at === null) {
                $pivot->attached_at = now();
            }
        });
    }
}
