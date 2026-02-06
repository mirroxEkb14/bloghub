<?php

namespace App\Filament\Resources\CreatorProfileResource\Schemas;

use App\Support\CreatorProfileResourceSupport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CreatorProfileResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->label(__('filament.creator_profiles.form.user_id'))
                    ->relationship('user', 'name', CreatorProfileResourceSupport::userRelationshipQuery())
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('display_name')
                    ->label(__('filament.creator_profiles.form.display_name'))
                    ->placeholder(__('filament.creator_profiles.form.display_name_placeholder'))
                    ->required()
                    ->maxLength(50)
                    ->live(onBlur: true)
                    ->afterStateUpdated(CreatorProfileResourceSupport::setSlugFromDisplayName()),
                TextInput::make('slug')
                    ->label(__('filament.creator_profiles.form.slug'))
                    ->hint(__('filament.creator_profiles.form.slug_auto_hint'))
                    ->required(),
                TextInput::make('about')
                    ->label(__('filament.creator_profiles.form.about'))
                    ->hint(__('filament.creator_profiles.form.about_hint'))
                    ->placeholder(__('filament.creator_profiles.form.about_placeholder'))
                    ->maxLength(255),
                FileUpload::make('profile_avatar_path')
                    ->label(__('filament.creator_profiles.form.profile_avatar_path'))
                    ->disk('public')
                    ->directory(CreatorProfileResourceSupport::AVATAR_DIRECTORY)
                    ->visibility('public')
                    ->acceptedFileTypes(CreatorProfileResourceSupport::acceptedImageMimeTypes())
                    ->maxSize(CreatorProfileResourceSupport::MAX_FILE_SIZE_KB)
                    ->image()
                    ->imageEditor(),
                FileUpload::make('profile_cover_path')
                    ->label(__('filament.creator_profiles.form.profile_cover_path'))
                    ->disk('public')
                    ->directory(CreatorProfileResourceSupport::COVER_DIRECTORY)
                    ->visibility('public')
                    ->acceptedFileTypes(CreatorProfileResourceSupport::acceptedImageMimeTypes())
                    ->maxSize(CreatorProfileResourceSupport::MAX_FILE_SIZE_KB)
                    ->image()
                    ->imageEditor(),
            ]);
    }
}
