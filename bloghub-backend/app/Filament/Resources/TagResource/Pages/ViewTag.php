<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource\TagResource;
use App\Support\TagResourceActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('creatorProfiles');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            TagResourceActions::deleteActionForRecord($this->record),
        ];
    }
}
