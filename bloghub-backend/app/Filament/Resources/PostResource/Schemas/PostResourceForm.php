<?php

namespace App\Filament\Resources\PostResource\Schemas;

use App\Models\Tier;
use App\Rules\SlugUniquePerCreatorProfileRule;
use App\Support\PostResourceSupport;
use App\Support\TierResourceSupport;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class PostResourceForm
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
                                Section::make(__('filament.posts.form.section_main'))
                                    ->columns()
                                    ->schema([
                                        Select::make('creator_profile_id')
                                            ->label(__('filament.posts.form.creator_profile_id'))
                                            ->relationship(
                                                'creatorProfile',
                                                'display_name',
                                                TierResourceSupport::creatorProfileRelationshipQuery()
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        TextInput::make('slug')
                                            ->label(__('filament.posts.form.slug'))
                                            ->placeholder(__('filament.posts.form.slug_placeholder'))
                                            ->hint(__('filament.posts.form.slug_hint'))
                                            ->required()
                                            ->maxLength(PostResourceSupport::SLUG_MAX_LENGTH)
                                            ->rules([SlugUniquePerCreatorProfileRule::forForm()]),
                                        TextInput::make('title')
                                            ->label(__('filament.posts.form.title'))
                                            ->placeholder(__('filament.posts.form.title_placeholder'))
                                            ->required()
                                            ->maxLength(PostResourceSupport::TITLE_MAX_LENGTH)
                                            ->columnSpanFull(),
                                        Textarea::make('content_text')
                                            ->label(__('filament.posts.form.content_text'))
                                            ->placeholder(__('filament.posts.form.content_text_placeholder'))
                                            ->hint(__('filament.posts.form.content_text_hint', ['max' => PostResourceSupport::CONTENT_TEXT_MAX_LENGTH]))
                                            ->required()
                                            ->minLength(PostResourceSupport::CONTENT_TEXT_MIN_LENGTH)
                                            ->maxLength(PostResourceSupport::CONTENT_TEXT_MAX_LENGTH)
                                            ->columnSpanFull()
                                            ->rows(10),
                                        Select::make('required_tier_id')
                                            ->label(__('filament.posts.form.required_tier_id'))
                                            ->options(function (Get $get): array {
                                                $creatorProfileId = $get('creator_profile_id');
                                                if (! $creatorProfileId) {
                                                    return [];
                                                }

                                                return Tier::query()
                                                    ->where('creator_profile_id', $creatorProfileId)
                                                    ->orderBy('level')
                                                    ->pluck('tier_name', 'id')
                                                    ->all();
                                            })
                                            ->searchable()
                                            ->nullable(),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.posts.form.section_media'))
                                    ->schema([
                                        TextInput::make('media_url')
                                            ->label(__('filament.posts.form.media_url'))
                                            ->placeholder(__('filament.posts.form.media_url_placeholder'))
                                            ->url()
                                            ->maxLength(PostResourceSupport::MEDIA_URL_MAX_LENGTH)
                                            ->columnSpanFull(),
                                        Select::make('media_type')
                                            ->label(__('filament.posts.form.media_type'))
                                            ->options(PostResourceSupport::mediaTypeOptions())
                                            ->nullable(),
                                    ]),
                                Section::make(__('filament.posts.form.section_metadata'))
                                    ->schema([
                                        TextInput::make('created_at')
                                            ->label(__('filament.posts.form.created_at'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                                        TextInput::make('updated_at')
                                            ->label(__('filament.posts.form.updated_at'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
