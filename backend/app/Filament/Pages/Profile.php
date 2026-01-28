<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

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
        $locale = auth()->user()?->locale ?? config('app.locale');
        $locale = $locale === 'cz' ? 'cs' : $locale;

        $this->form->fill([
            'locale' => $locale,
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
                        'cs' => __('filament.profile.language_options.cs'),
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

        $user->forceFill([
            'locale' => $data['locale'] === 'cz' ? 'cs' : $data['locale'],
        ])->save();

        Notification::make()
            ->title(__('filament.profile.saved'))
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
