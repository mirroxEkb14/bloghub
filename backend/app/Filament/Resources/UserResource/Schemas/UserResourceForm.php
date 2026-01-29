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
                    ->hint(__('filament.users.form.name_helper'))
                    ->placeholder(__('filament.users.form.name_placeholder'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('username')
                    ->label(__('filament.users.form.username'))
                    ->hint(__('filament.users.form.username_helper'))
                    ->placeholder(__('filament.users.form.username_placeholder'))
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('filament.users.form.email'))
                    ->hint(__('filament.users.form.email_helper'))
                    ->placeholder(__('filament.users.form.email_placeholder'))
                    ->required()
                    ->rules([new EmailRule()])
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('filament.users.form.phone'))
                    ->hint(__('filament.users.form.phone_helper'))
                    ->placeholder(__('filament.users.form.phone_placeholder'))
                    ->prefix('+')
                    ->rules([new PhoneRule()])
                    ->maxLength(20),
                TextInput::make('password')
                    ->label(__('filament.users.form.password'))
                    ->hint(__('filament.users.form.password_helper'))
                    ->validationAttribute(__('filament.users.form.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
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
                    ->dehydrated(fn (?string $state, string $operation): bool =>
                        $operation === 'create' || filled($state)
                    ),
                Toggle::make('is_creator')
                    ->label(__('filament.users.form.is_creator'))
                    ->default(false),
            ]);
    }
}
