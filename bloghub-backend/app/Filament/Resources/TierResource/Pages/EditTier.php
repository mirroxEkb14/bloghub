<?php

namespace App\Filament\Resources\TierResource\Pages;

use App\Filament\Resources\TierResource\TierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTier extends EditRecord
{
    protected static string $resource = TierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }

    protected function afterSave(): void
    {
        $this->fillForm();
    }
}
