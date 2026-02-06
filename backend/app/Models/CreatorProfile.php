<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreatorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'display_name',
        'about',
        'profile_avatar_path',
        'profile_cover_path',
    ];

    protected static function booted(): void
    {
        static::saving(function (CreatorProfile $profile): void {
            if (filled($profile->display_name)) {
                $profile->slug = $profile->generateUniqueSlug();
            }
        });

        static::created(function (CreatorProfile $profile): void {
            $profile->user?->update(['is_creator' => true]);
        });

        static::deleted(function (CreatorProfile $profile): void {
            $profile->user?->update(['is_creator' => false]);
        });
    }

    public function generateUniqueSlug(): string
    {
        $base = Str::slug($this->display_name ?? '');
        if ($base === '') {
            $base = 'creator';
        }
        $slug = $base;
        $count = 1;
        while (static::query()->where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists()) {
            $slug = $base.'-'.($count++);
        }
        return $slug;
    }

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

    public function getProfileAvatarUrlAttribute(): ?string
    {
        $path = $this->attributes['profile_avatar_path'] ?? null;

        return $path !== null && $path !== ''
            ? Storage::disk('public')->url($path)
            : null;
    }

    public function getProfileCoverUrlAttribute(): ?string
    {
        $path = $this->attributes['profile_cover_path'] ?? null;

        return $path !== null && $path !== ''
            ? Storage::disk('public')->url($path)
            : null;
    }
}
