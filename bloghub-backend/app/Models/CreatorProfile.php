<?php

namespace App\Models;

use App\Support\StorageUrlSupport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public static function uniqueSlugForDisplayName(string $displayName, ?int $excludeId = null): string
    {
        $base = Str::slug($displayName);
        if ($base === '') {
            $base = 'creator';
        }
        $slug = $base;
        $count = 1;
        while (static::query()->where('slug', $slug)->when($excludeId !== null, fn ($q) => $q->whereKeyNot($excludeId))->exists()) {
            $slug = $base.'-'.($count++);
        }

        return $slug;
    }

    public function generateUniqueSlug(): string
    {
        return static::uniqueSlugForDisplayName($this->display_name ?? '', $this->exists ? $this->getKey() : null);
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'creator_profile_tag')
            ->using(CreatorProfileTag::class)
            ->withPivot('attached_at');
    }

    public function getProfileAvatarUrlAttribute(): ?string
    {
        return StorageUrlSupport::publicUrl($this->attributes['profile_avatar_path'] ?? null);
    }

    public function getProfileCoverUrlAttribute(): ?string
    {
        return StorageUrlSupport::publicUrl($this->attributes['profile_cover_path'] ?? null);
    }
}
