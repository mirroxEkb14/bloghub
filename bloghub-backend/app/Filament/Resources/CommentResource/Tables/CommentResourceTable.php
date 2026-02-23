<?php

namespace App\Filament\Resources\CommentResource\Tables;

use App\Filament\Resources\CommentResource\CommentResource;
use App\Filters\CommentTableFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class CommentResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => CommentResource::getUrl('view', ['record' => $record]))
            ->defaultSort('id', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'post']))
            ->filters(CommentTableFilters::filters())
            ->reorderableColumns()
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('user.name')
                    ->label(__('filament.comments.table.columns.user'))
                    ->view('filament.tables.columns.comment-user')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post.title')
                    ->label(__('filament.comments.table.columns.post'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('content_text')
                    ->label(__('filament.comments.table.columns.content_text'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.comments.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.comments.table.actions.view')),
                DeleteAction::make()->label(__('filament.comments.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
