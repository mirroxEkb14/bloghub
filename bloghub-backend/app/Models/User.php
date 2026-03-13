<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use App\Rules\PhoneRule;
use App\Support\StorageUrlSupport;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar_path',
        'phone',
        'password',
        'locale',
        'is_creator',
        'terms_accepted_at',
        'privacy_accepted_at',
        'stripe_customer_id',
    ];

    protected $appends = ['avatar_url'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_creator' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return StorageUrlSupport::publicUrl($this->attributes['avatar_path'] ?? null);
    }

    public function creatorProfile(): HasOne
    {
        return $this->hasOne(CreatorProfile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function followingCreatorProfiles(): BelongsToMany
    {
        return $this->belongsToMany(CreatorProfile::class, 'creator_profile_follows', 'user_id', 'creator_profile_id')
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = PhoneRule::normalize($value);
    }

    public function hasAcceptedTerms(): bool
    {
        return ! is_null($this->terms_accepted_at);
    }

    public function hasAcceptedPrivacyPolicy(): bool
    {
        return ! is_null($this->privacy_accepted_at);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }
}
