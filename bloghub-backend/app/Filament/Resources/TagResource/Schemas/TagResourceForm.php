<?php

namespace App\Filament\Resources\TagResource\Schemas;

use App\Support\TagResourceSupport;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.tags.form.section_details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.tags.form.name'))
                            ->placeholder(__('filament.tags.form.name_placeholder'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(TagResourceSupport::NAME_MAX_LENGTH)
                            ->live(onBlur: true)
                            ->afterStateUpdated(TagResourceSupport::setSlugFromName()),
                        TextInput::make('slug')
                            ->label(__('filament.tags.form.slug'))
                            ->placeholder(__('filament.tags.form.slug_placeholder'))
                            ->hint(__('filament.tags.form.slug_auto_hint'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(TagResourceSupport::SLUG_MAX_LENGTH)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    ])
                    ->columns(2),
                Section::make(__('filament.tags.table.columns.creator_profiles'))
                    ->schema([
                        TextInput::make('creator_profiles_count')
                            ->label(__('filament.tags.form.creator_profiles_count_label'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state, $record) => $record ? (string) $record->creatorProfiles()->count() : '')
                            ->placeholder('0')
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->visible(fn ($record) => $record !== null),
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
            ]);
    }
}
