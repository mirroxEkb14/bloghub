<?php

namespace App\Filament\Resources\CreatorProfileResource\Schemas;

use App\Support\CreatorProfileResourceSupport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreatorProfileResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Hidden::make('id'),
                Grid::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.creator_profiles.form.section_profile'))
                                    ->columns()
                                    ->schema([
                                        Select::make('user_id')
                                            ->label(__('filament.creator_profiles.form.user_id'))
                                            ->relationship('user', 'name', CreatorProfileResourceSupport::userRelationshipQuery())
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('slug')
                                            ->label(__('filament.creator_profiles.form.slug'))
                                            ->helperText(__('filament.creator_profiles.form.slug_auto_hint'))
                                            ->required(),
                                        FileUpload::make('profile_avatar_path')
                                            ->label(__('filament.creator_profiles.form.profile_avatar_path'))
                                            ->disk('public')
                                            ->directory(CreatorProfileResourceSupport::AVATAR_DIRECTORY)
                                            ->visibility('public')
                                            ->acceptedFileTypes(CreatorProfileResourceSupport::acceptedImageMimeTypes())
                                            ->maxSize(CreatorProfileResourceSupport::MAX_FILE_SIZE_KB)
                                            ->image()
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.creator_profiles.form.section_display'))
                                    ->columns()
                                    ->schema([
                                        TextInput::make('display_name')
                                            ->label(__('filament.creator_profiles.form.display_name'))
                                            ->placeholder(__('filament.creator_profiles.form.display_name_placeholder'))
                                            ->required()
                                            ->maxLength(CreatorProfileResourceSupport::DISPLAY_NAME_MAX_LENGTH)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(CreatorProfileResourceSupport::setSlugFromDisplayName()),
                                        Select::make('tags')
                                            ->label(__('filament.creator_profiles.form.tags'))
                                            ->relationship('tags', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload(),
                                        FileUpload::make('profile_cover_path')
                                            ->label(__('filament.creator_profiles.form.profile_cover_path'))
                                            ->disk('public')
                                            ->directory(CreatorProfileResourceSupport::COVER_DIRECTORY)
                                            ->visibility('public')
                                            ->acceptedFileTypes(CreatorProfileResourceSupport::acceptedImageMimeTypes())
                                            ->maxSize(CreatorProfileResourceSupport::MAX_FILE_SIZE_KB)
                                            ->image()
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
                Section::make(__('filament.creator_profiles.form.section_about'))
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('about')
                            ->label(__('filament.creator_profiles.form.about'))
                            ->hint(__('filament.creator_profiles.form.about_hint'))
                            ->placeholder(__('filament.creator_profiles.form.about_placeholder'))
                            ->maxLength(CreatorProfileResourceSupport::ABOUT_MAX_LENGTH)
                            ->rows(8)
                            ->columnSpanFull()
                            ->extraInputAttributes(['class' => 'max-h-[20rem] overflow-y-auto']),
                    ]),
            ]);
    }
}
