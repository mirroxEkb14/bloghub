<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('requiredTier');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
