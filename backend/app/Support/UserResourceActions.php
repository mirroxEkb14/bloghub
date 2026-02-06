<?php

namespace App\Support;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class UserResourceActions
{
    public static function deleteActionForTable(): DeleteAction
    {
        return DeleteAction::make()
            ->label(__('filament.users.table.actions.delete'))
            ->requiresConfirmation()
            ->authorize(fn (User $record): bool => self::canShowDeleteAction($record))
            ->disabled(fn (User $record): bool => self::isCurrentUser($record))
            ->tooltip(fn (User $record): ?string => self::cannotDeleteYourselfTooltip($record))
            ->before(function (DeleteAction $action, User $record): void {
                self::haltDeleteSelf($action, $record);
            });
    }

    public static function deleteActionForRecord(Model $record): DeleteAction
    {
        assert($record instanceof User);

        return DeleteAction::make()
            ->requiresConfirmation()
            ->authorize(fn (): bool => self::canShowDeleteAction($record))
            ->disabled(fn (): bool => self::isCurrentUser($record))
            ->tooltip(fn (): ?string => self::cannotDeleteYourselfTooltip($record))
            ->before(function (DeleteAction $action) use ($record): void {
                self::haltDeleteSelf($action, $record);
            });
    }

    private static function isCurrentUser(User $record): bool
    {
        return $record->id === auth()->id();
    }

    private static function canShowDeleteAction(User $record): bool
    {
        return self::isCurrentUser($record) || auth()->user()->can('delete', $record);
    }

    private static function cannotDeleteYourselfTooltip(User $record): ?string
    {
        return self::isCurrentUser($record) ? __('filament.users.cannot_delete_yourself') : null;
    }

    private static function haltDeleteSelf(DeleteAction $action, User $record): void
    {
        if (self::isCurrentUser($record)) {
            Notification::make()
                ->title(__('filament.users.cannot_delete_yourself'))
                ->danger()
                ->send();
            $action->halt();
        }
    }
}
