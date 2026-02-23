<?php

namespace App\Filament\Resources\CommentResource\Schemas;

use App\Support\CommentResourceSupport;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentResourceForm
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
                                Section::make(__('filament.comments.form.section_main'))
                                    ->columns()
                                    ->schema([
                                        Select::make('user_id')
                                            ->label(__('filament.comments.form.user_id'))
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(),
                                        Select::make('post_id')
                                            ->label(__('filament.comments.form.post_id'))
                                            ->relationship('post', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(),
                                        Textarea::make('content_text')
                                            ->label(__('filament.comments.form.content_text'))
                                            ->placeholder(__('filament.comments.form.content_text_placeholder'))
                                            ->hint(__('filament.comments.form.content_text_hint', ['max' => CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH]))
                                            ->required()
                                            ->maxLength(CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH)
                                            ->columnSpanFull()
                                            ->rows(6),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament.comments.form.section_metadata'))
                                    ->schema([
                                        TextInput::make('created_at')
                                            ->label(__('filament.comments.form.created_at'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                                        TextInput::make('updated_at')
                                            ->label(__('filament.comments.form.updated_at'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
