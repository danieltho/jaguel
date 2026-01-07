<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('lastname')
                            ->label('Apellido')
                            ->maxLength(255),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->maxLength(20),
                    ]),

                Section::make('Credenciales')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255),
                    ]),

                Section::make('Contacto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(20),
                    ]),

                Section::make('Dirección')
                    ->columns(2)
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(100),
                        TextInput::make('province')
                            ->label('Provincia')
                            ->maxLength(100),
                        TextInput::make('country')
                            ->label('País')
                            ->default('Argentina')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Section::make('Preferencias')
                    ->schema([
                        Toggle::make('receive_offers')
                            ->label('Recibir ofertas y promociones')
                            ->default(false),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email verificado'),
                    ]),
            ]);
    }
}
