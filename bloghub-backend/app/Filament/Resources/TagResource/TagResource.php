<?php

namespace App\Filament\Resources\TagResource;

use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Filament\Resources\TagResource\Pages\ViewTag;
use App\Filament\Resources\TagResource\Schemas\TagResourceForm;
use App\Filament\Resources\TagResource\Tables\TagResourceTable;
use App\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('filament.tags.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.administration.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.tags.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.tags.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return TagResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'view' => ViewTag::route('/{record}'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
