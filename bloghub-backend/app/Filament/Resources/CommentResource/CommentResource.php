<?php

namespace App\Filament\Resources\CommentResource;

use App\Filament\Resources\CommentResource\Pages\ListComments;
use App\Filament\Resources\CommentResource\Pages\ViewComment;
use App\Filament\Resources\CommentResource\Schemas\CommentResourceForm;
use App\Filament\Resources\CommentResource\Tables\CommentResourceTable;
use App\Models\Comment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('filament.comments.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.comments.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.comments.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CommentResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
            'view' => ViewComment::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
