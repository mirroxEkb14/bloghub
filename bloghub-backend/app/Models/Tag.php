<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'slug',
        'name',
    ];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag): void {
            if (filled($tag->name) && (blank($tag->slug) || $tag->isDirty('name'))) {
                $tag->slug = $tag->generateUniqueSlug();
            }
        });
    }

    public static function uniqueSlugForName(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'tag';
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
        return static::uniqueSlugForName($this->name ?? '', $this->exists ? $this->getKey() : null);
    }

    public function creatorProfiles(): BelongsToMany
    {
        return $this->belongsToMany(CreatorProfile::class, 'creator_profile_tag')
            ->using(CreatorProfileTag::class)
            ->withPivot('attached_at');
    }

    public function getCreatorProfilesLabelAttribute(): string
    {
        $profiles = $this->creatorProfiles;
        if ($profiles->isEmpty()) {
            return '';
        }

        return $profiles->map(fn ($profile) => "#{$profile->id} · {$profile->display_name}")->join(', ');
    }
}
