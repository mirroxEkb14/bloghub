<?php

namespace App\Filament\Resources\CreatorProfileResource;

use App\Filament\Resources\CreatorProfileResource\Pages\CreateCreatorProfile;
use App\Filament\Resources\CreatorProfileResource\Pages\EditCreatorProfile;
use App\Filament\Resources\CreatorProfileResource\Pages\ListCreatorProfiles;
use App\Filament\Resources\CreatorProfileResource\Pages\ViewCreatorProfile;
use App\Filament\Resources\CreatorProfileResource\Schemas\CreatorProfileResourceForm;
use App\Filament\Resources\CreatorProfileResource\Tables\CreatorProfileResourceTable;
use App\Models\CreatorProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CreatorProfileResource extends Resource
{
    protected static ?string $model = CreatorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('filament.creator_profiles.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.creator_profiles.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.creator_profiles.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CreatorProfileResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreatorProfileResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreatorProfiles::route('/'),
            'create' => CreateCreatorProfile::route('/create'),
            'view' => ViewCreatorProfile::route('/{record}'),
            'edit' => EditCreatorProfile::route('/{record}/edit'),
        ];
    }
}
