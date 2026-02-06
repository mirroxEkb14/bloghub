<?php

namespace App\Support;

use App\Filament\Resources\UserResource\UserResource;
use App\Models\User;
use Closure;

class UserResourceSupport
{
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
