<?php

namespace App\Filament\Resources\TierResource;

use App\Filament\Resources\TierResource\Pages\CreateTier;
use App\Filament\Resources\TierResource\Pages\EditTier;
use App\Filament\Resources\TierResource\Pages\ListTiers;
use App\Filament\Resources\TierResource\Pages\ViewTier;
use App\Filament\Resources\TierResource\Schemas\TierResourceForm;
use App\Filament\Resources\TierResource\Tables\TierResourceTable;
use App\Models\Tier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TierResource extends Resource
{
    protected static ?string $model = Tier::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('filament.tiers.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.tiers.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.tiers.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return TierResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TierResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTiers::route('/'),
            'create' => CreateTier::route('/create'),
            'view' => ViewTier::route('/{record}'),
            'edit' => EditTier::route('/{record}/edit'),
        ];
    }
}
