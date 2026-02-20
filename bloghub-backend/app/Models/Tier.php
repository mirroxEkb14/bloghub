<?php

namespace App\Models;

use App\Enums\Currency;
use App\Support\StorageUrlSupport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tier extends Model
{
    protected $fillable = [
        'creator_profile_id',
        'level',
        'tier_name',
        'tier_desc',
        'price',
        'tier_currency',
        'tier_cover_path',
    ];

    protected $casts = [
        'tier_currency' => Currency::class,
    ];

    public function creatorProfile(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function requiredByPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'required_tier_id');
    }

    public function getTierCoverUrlAttribute(): ?string
    {
        return StorageUrlSupport::publicUrl($this->attributes['tier_cover_path'] ?? null);
    }
}
