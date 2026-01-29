<?php

namespace App\Filament\Resources\UserResource\Schemas;

use App\Rules\EmailRule;
use App\Rules\PhoneRule;
use App\Rules\PasswordNotContainingUserData;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('filament.users.form.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label(__('filament.users.form.username'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('filament.users.form.email'))
                    ->required()
                    ->rules([new EmailRule()])
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('filament.users.form.phone'))
                    ->rules([new PhoneRule()])
                    ->validationMessages([
                        'phone' => __('validation.phone'),
                        'regex' => __('validation.phone'),
                    ])
                    ->maxLength(255),
                TextInput::make('password')
                    ->label(__('filament.users.form.password'))
                    ->validationAttribute(__('filament.users.form.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->nullable()
                    ->maxLength(255)
                    ->validationMessages([
                        'min' => __('validation.password.min'),
                    ])
                    ->rules([
                        Password::min(8)
                            ->letters()
                            ->numbers()
                            ->symbols()
                            ->mixedCase()
                            ->uncompromised(),
                        fn (Get $get) => new PasswordNotContainingUserData([
                            'email' => [
                                'value' => $get('email'),
                                'label' => __('filament.users.form.email'),
                            ],
                            'username' => [
                                'value' => $get('username'),
                                'label' => __('filament.users.form.username'),
                            ],
                            'name' => [
                                'value' => $get('name'),
                                'label' => __('filament.users.form.name'),
                            ],
                        ]),
                    ])
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Toggle::make('is_creator')
                    ->label(__('filament.users.form.is_creator'))
                    ->default(false),
            ]);
    }
}
