<?php

namespace App\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CreatorProfileTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('user_id')
                ->label(__('filament.creator_profiles.table.filters.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
            Filter::make('posts_count')
                ->label(__('filament.creator_profiles.table.filters.posts_count'))
                ->schema([
                    TextInput::make('value')
                        ->label(__('filament.creator_profiles.table.filters.posts_count'))
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->step(1)
                        ->placeholder('0'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;
                    if ($value === null || $value === '') {
                        return $query;
                    }
                    return $query->withCount('posts')->having('posts_count', '=', (int) $value);
                })
                ->indicateUsing(function (array $data): ?string {
                    $value = $data['value'] ?? null;
                    if ($value === null || $value === '') {
                        return null;
                    }
                    return __('filament.creator_profiles.table.filters.posts_count_indicator', ['count' => $value]);
                }),
        ];
    }
}
