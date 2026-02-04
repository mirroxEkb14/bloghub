<?php

namespace App\Models;

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
        'currency',
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
}
