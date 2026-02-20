<?php

namespace App\Filament\Resources\TierResource\Pages;

use App\Filament\Resources\TierResource\TierResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTier extends ViewRecord
{
    protected static string $resource = TierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
