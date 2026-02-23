<?php

namespace App\Filament\Resources\PostResource;

use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PostResource\Pages\ViewPost;
use App\Filament\Resources\PostResource\Schemas\PostResourceForm;
use App\Filament\Resources\PostResource\Tables\PostResourceTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('filament.posts.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.posts.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.posts.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return PostResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'view' => ViewPost::route('/{record}'),
        ];
    }
}
