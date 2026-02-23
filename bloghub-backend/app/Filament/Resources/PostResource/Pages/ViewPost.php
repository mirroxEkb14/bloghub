<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
