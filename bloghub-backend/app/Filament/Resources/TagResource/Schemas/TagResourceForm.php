<?php

namespace App\Filament\Resources\TagResource\Schemas;

use App\Support\TagResourceSupport;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
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
            ]);
    }
}
