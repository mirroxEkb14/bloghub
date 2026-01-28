<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as SchemaFacade;

class Profile extends Page
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 100;
    protected string $view = 'filament.pages.profile';

    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('filament.profile.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament.profile.navigation_label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'locale' => auth()->user()?->locale ?? config('app.locale'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('locale')
                    ->label(__('filament.profile.language_label'))
                    ->options([
                        'en' => __('filament.profile.language_options.en'),
                        'cz' => __('filament.profile.language_options.cz'),
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if (! SchemaFacade::hasColumn('users', 'locale')) {
            Notification::make()
                ->title(__('filament.profile.missing_locale_column'))
                ->danger()
                ->send();

            return;
        }

        $user->forceFill([
            'locale' => $data['locale'],
        ])->save();

        Notification::make()
            ->title(__('filament.profile.saved'))
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
