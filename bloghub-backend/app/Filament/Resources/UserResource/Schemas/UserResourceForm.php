<?php

namespace App\Filament\Resources\UserResource\Schemas;

use App\Rules\EmailRule;
use App\Rules\PhoneRule;
use App\Rules\PasswordWithoutUserDataRule;
use App\Support\UserResourceSupport;
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
                    ->maxLength(UserResourceSupport::NAME_MAX_LENGTH),
                TextInput::make('username')
                    ->label(__('filament.users.form.username'))
                    ->hint(__('filament.users.form.username_helper'))
                    ->placeholder(__('filament.users.form.username_placeholder'))
                    ->required()
                    ->maxLength(UserResourceSupport::USERNAME_MAX_LENGTH)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('filament.users.form.email'))
                    ->hint(__('filament.users.form.email_helper'))
                    ->placeholder(__('filament.users.form.email_placeholder'))
                    ->required()
                    ->rules([new EmailRule()])
                    ->maxLength(UserResourceSupport::EMAIL_MAX_LENGTH)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('filament.users.form.phone'))
                    ->hint(__('filament.users.form.phone_helper'))
                    ->placeholder(__('filament.users.form.phone_placeholder'))
                    ->prefix('+')
                    ->formatStateUsing(UserResourceSupport::stripLeadingPlus())
                    ->rules([new PhoneRule()])
                    ->maxLength(UserResourceSupport::PHONE_MAX_LENGTH),
                TextInput::make('password')
                    ->label(__('filament.users.form.password'))
                    ->hint(__('filament.users.form.password_helper'))
                    ->validationAttribute(__('filament.users.form.password'))
                    ->password()
                    ->required(UserResourceSupport::requiredOnCreate())
                    ->maxLength(UserResourceSupport::PASSWORD_MAX_LENGTH)
                    ->rules([
                        Password::min(UserResourceSupport::PASSWORD_MIN_LENGTH)
                            ->letters()
                            ->numbers()
                            ->symbols()
                            ->mixedCase()
                            ->uncompromised(),
                        fn (Get $get) => PasswordWithoutUserDataRule::fromGet($get),
                    ])
                    ->dehydrated(UserResourceSupport::dehydratedOnCreateOrFilled()),
                Toggle::make('is_creator')
                    ->label(__('filament.users.form.is_creator'))
                    ->default(false),
            ]);
    }
}
