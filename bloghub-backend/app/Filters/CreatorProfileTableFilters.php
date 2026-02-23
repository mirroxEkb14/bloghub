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
                    TextInput::make('from')
                        ->label(__('filament.creator_profiles.table.filters.posts_count_from'))
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->step(1)
                        ->placeholder(__('filament.creator_profiles.table.filters.posts_count_from_placeholder')),
                    TextInput::make('to')
                        ->label(__('filament.creator_profiles.table.filters.posts_count_to'))
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->step(1)
                        ->placeholder(__('filament.creator_profiles.table.filters.posts_count_to_placeholder')),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $base = $data['posts_count'] ?? $data;
                    $from = isset($base['from']) && $base['from'] !== '' ? (int) $base['from'] : null;
                    $to = isset($base['to']) && $base['to'] !== '' ? (int) $base['to'] : null;
                    if ($from === null && $to === null) {
                        return $query;
                    }
                    $subquery = '(select count(*) from posts where posts.creator_profile_id = creator_profiles.id)';
                    if ($from !== null) {
                        $query->whereRaw($subquery.' >= ?', [$from]);
                    }
                    if ($to !== null) {
                        $query->whereRaw($subquery.' <= ?', [$to]);
                    }

                    return $query;
                })
                ->indicateUsing(function (array $data): ?string {
                    $base = $data['posts_count'] ?? $data;
                    $from = isset($base['from']) && $base['from'] !== '' ? (int) $base['from'] : null;
                    $to = isset($base['to']) && $base['to'] !== '' ? (int) $base['to'] : null;
                    if ($from === null && $to === null) {
                        return null;
                    }
                    if ($from !== null && $to !== null) {
                        return __('filament.creator_profiles.table.filters.posts_count_indicator_range', ['from' => $from, 'to' => $to]);
                    }
                    if ($from !== null) {
                        return __('filament.creator_profiles.table.filters.posts_count_indicator_from', ['from' => $from]);
                    }

                    return __('filament.creator_profiles.table.filters.posts_count_indicator_to', ['to' => $to]);
                }),
        ];
    }
}
