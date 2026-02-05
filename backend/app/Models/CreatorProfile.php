<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreatorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'display_name',
        'about',
        'profile_avatar_url',
        'profile_cover_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(Tier::class);
    }
}
