<?php

namespace App\Support;

use App\Models\Tag;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class TagResourceActions
{
    public static function deleteActionForTable(): DeleteAction
    {
        return DeleteAction::make()
            ->label(__('filament.tags.table.actions.delete'))
            ->requiresConfirmation()
            ->disabled(fn (Tag $record): bool => self::isUsedByCreatorProfiles($record))
            ->tooltip(fn (Tag $record): ?string => self::cannotDeleteTooltip($record))
            ->before(function (DeleteAction $action, Tag $record): void {
                self::haltIfInUse($action, $record);
            });
    }

    public static function deleteActionForRecord(Model $record): DeleteAction
    {
        assert($record instanceof Tag);

        return DeleteAction::make()
            ->requiresConfirmation()
            ->disabled(fn (): bool => self::isUsedByCreatorProfiles($record))
            ->tooltip(fn (): ?string => self::cannotDeleteTooltip($record))
            ->before(function (DeleteAction $action) use ($record): void {
                self::haltIfInUse($action, $record);
            });
    }

    private static function isUsedByCreatorProfiles(Tag $record): bool
    {
        return $record->creatorProfiles()->exists();
    }

    private static function cannotDeleteTooltip(Tag $record): ?string
    {
        return self::isUsedByCreatorProfiles($record)
            ? __('filament.tags.cannot_delete_in_use')
            : null;
    }

    private static function haltIfInUse(DeleteAction $action, Tag $record): void
    {
        if (self::isUsedByCreatorProfiles($record)) {
            Notification::make()
                ->title(__('filament.tags.cannot_delete_in_use'))
                ->danger()
                ->send();
            $action->halt();
        }
    }
}
