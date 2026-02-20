<?php

namespace App\Support;

use App\Filament\Resources\UserResource\UserResource;
use App\Models\User;
use Closure;

class UserResourceSupport
{
    public const NAME_MAX_LENGTH = 100;
    public const USERNAME_MAX_LENGTH = 50;
    public const EMAIL_MAX_LENGTH = 255;
    public const PHONE_MAX_LENGTH = 20;
    public const PASSWORD_MAX_LENGTH = 255;
    public const PASSWORD_MIN_LENGTH = 8;

    private function __construct()
    {
    }

    public static function recordViewUrl(User $record): string
    {
        return UserResource::getUrl('view', ['record' => $record]);
    }

    public static function requiredOnCreate(): Closure
    {
        return static fn (string $operation): bool => $operation === 'create';
    }

    public static function dehydratedOnCreateOrFilled(): Closure
    {
        return static fn (?string $state, string $operation): bool => $operation === 'create' || filled($state);
    }

    public static function stripLeadingPlus(): Closure
    {
        return static fn (?string $state): ?string => $state === null ? null : ltrim($state, '+');
    }
}
