<?php

namespace App\Filament\Resources\SubscriptionResource\Schemas;

use App\Support\SubscriptionResourceSupport;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Hidden::make('id'),
                Grid::make()
                    ->columnSpanFull()
                    ->columns()
                    ->schema([
                        Section::make(__('filament.subscriptions.form.section_main'))
                            ->columns(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('filament.subscriptions.form.user_id'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('sub_status')
                                    ->label(__('filament.subscriptions.form.sub_status'))
                                    ->options(SubscriptionResourceSupport::subStatusOptions())
                                    ->required(),
                                Select::make('tier_id')
                                    ->label(__('filament.subscriptions.form.tier_id'))
                                    ->relationship('tier', 'tier_name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('tier_level')
                                    ->label(__('filament.tiers.form.level'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($get) => $get('tier_level') !== null && $get('tier_level') !== ''),
                                TextInput::make('start_date')
                                    ->label(__('filament.subscriptions.form.start_date'))
                                    ->placeholder(__('filament.subscriptions.form.start_date_placeholder'))
                                    ->default(fn () => now()->format('Y-m-d H:i'))
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                                TextInput::make('end_date')
                                    ->label(__('filament.subscriptions.form.end_date'))
                                    ->placeholder(__('filament.subscriptions.form.end_date_placeholder'))
                                    ->required()
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                            ]),
                        Section::make(__('filament.section_metadata'))
                            ->schema([
                                TextInput::make('created_at')
                                    ->label(__('filament.fields.created_at'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : '—'),
                                TextInput::make('updated_at')
                                    ->label(__('filament.fields.updated_at'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : '—'),
                            ])
                            ->columns(2)
                            ->visible(fn ($record) => $record !== null),
                    ]),
            ]);
    }
}
