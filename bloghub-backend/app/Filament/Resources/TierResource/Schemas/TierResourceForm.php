<?php

namespace App\Filament\Resources\TierResource\Schemas;

use App\Rules\LevelUniquePerCreatorProfileRule;
use App\Support\TierResourceSupport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TierResourceForm
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
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.tiers.form.section_main'))
                                    ->columns()
                                    ->schema([
                                        Select::make('creator_profile_id')
                                            ->label(__('filament.tiers.form.creator_profile_id'))
                                            ->relationship(
                                                'creatorProfile',
                                                'display_name',
                                                TierResourceSupport::creatorProfileRelationshipQuery()
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('level')
                                            ->label(__('filament.tiers.form.level'))
                                            ->options(TierResourceSupport::levelOptions())
                                            ->required()
                                            ->rules([LevelUniquePerCreatorProfileRule::forForm()]),
                                        TextInput::make('tier_name')
                                            ->label(__('filament.tiers.form.tier_name'))
                                            ->placeholder(__('filament.tiers.form.tier_name_placeholder'))
                                            ->required()
                                            ->maxLength(TierResourceSupport::NAME_MAX_LENGTH)
                                            ->columnSpanFull(),
                                        TextInput::make('tier_desc')
                                            ->label(__('filament.tiers.form.tier_desc'))
                                            ->placeholder(__('filament.tiers.form.tier_desc_placeholder'))
                                            ->required()
                                            ->maxLength(TierResourceSupport::DESC_MAX_LENGTH)
                                            ->columnSpanFull(),
                                        FileUpload::make('tier_cover_path')
                                            ->label(__('filament.tiers.form.tier_cover_path'))
                                            ->disk('public')
                                            ->directory(TierResourceSupport::COVER_DIRECTORY)
                                            ->visibility('public')
                                            ->acceptedFileTypes(TierResourceSupport::acceptedCoverImageMimeTypes())
                                            ->maxSize(TierResourceSupport::COVER_MAX_FILE_SIZE_KB)
                                            ->image()
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.tiers.form.section_pricing'))
                                    ->schema([
                                        TextInput::make('price')
                                            ->label(__('filament.tiers.form.price'))
                                            ->placeholder(__('filament.tiers.form.price_placeholder'))
                                            ->required()
                                            ->integer()
                                            ->minValue(1),
                                        Select::make('tier_currency')
                                            ->label(__('filament.tiers.form.tier_currency'))
                                            ->options(TierResourceSupport::currencyOptions())
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
