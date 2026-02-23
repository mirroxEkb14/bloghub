<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'creator_profile_id',
        'required_tier_id',
        'slug',
        'title',
        'content_text',
        'media_url',
        'media_type',
    ];

    protected $casts = [
        'media_type' => MediaType::class,
    ];

    public function creatorProfile(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class);
    }

    public function requiredTier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'required_tier_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
