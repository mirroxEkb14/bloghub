<?php

namespace App\Filament\Resources\PostResource\Tables;

use App\Filament\Resources\PostResource\PostResource;
use App\Filters\PostTableFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class PostResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => PostResource::getUrl('view', ['record' => $record]))
            ->defaultSort('id')
            ->modifyQueryUsing(fn ($query) => $query->with(['creatorProfile.user', 'requiredTier']))
            ->filters(PostTableFilters::filters())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('creatorProfile.display_name')
                    ->label(__('filament.posts.table.columns.creator_profile'))
                    ->view('filament.tables.columns.post-creator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('filament.posts.table.columns.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('slug')
                    ->label(__('filament.posts.table.columns.slug'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('media_type')
                    ->label(__('filament.posts.table.columns.media_type'))
                    ->formatStateUsing(fn ($state) => $state?->value ?? $state)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('requiredTier.level')
                    ->label(__('filament.posts.table.columns.required_tier'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('comments_count')
                    ->label(__('filament.posts.table.columns.comments_count'))
                    ->counts('comments')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.posts.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.posts.table.actions.view')),
                DeleteAction::make()->label(__('filament.posts.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
