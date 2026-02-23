<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource\TagResource;
use App\Support\TagResourceActions;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TagResourceActions::deleteActionForRecord($this->record),
        ];
    }
}
