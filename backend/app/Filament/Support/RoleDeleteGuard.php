<?php

namespace App\Filament\Support;

use Spatie\Permission\Models\Role;

class RoleDeleteGuard
{
    public static function isDisabled(mixed $record): bool
    {
        return $record instanceof Role && $record->users()->exists();
    }

    public static function tooltip(mixed $record): ?string
    {
        if ($record instanceof Role && $record->users()->exists()) {
            return 'This role cannot be deleted because it is assigned to at least one user.';
        }

        return null;
    }

    public static function configure(object $action): void
    {
        $action
            ->disabled(static fn (mixed $record): bool => self::isDisabled($record))
            ->tooltip(static fn (mixed $record): ?string => self::tooltip($record));
    }
}
