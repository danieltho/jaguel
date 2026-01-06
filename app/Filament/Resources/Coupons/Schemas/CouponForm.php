<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponScopeEnum;
use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Tipo')
                            ->options(CouponTypeEnum::class)
                            ->default(CouponTypeEnum::COUPON)
                            ->required()
                            ->live(),

                        TextInput::make('code')
                            ->label('Código del Cupón')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->visible(fn ($get) => $get('type') === CouponTypeEnum::COUPON->value)
                            ->required(fn ($get) => $get('type') === CouponTypeEnum::COUPON->value)
                            ->alphaDash()
                            ->helperText('Solo letras, números, guiones y guiones bajos'),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Configuración del Descuento')
                    ->schema([
                        Select::make('discount_type')
                            ->label('Tipo de Descuento')
                            ->options(DiscountTypeEnum::class)
                            ->required()
                            ->live(),

                        TextInput::make('discount_value')
                            ->label(fn ($get) => $get('discount_type') === DiscountTypeEnum::PERCENTAGE->value
                                ? 'Porcentaje de descuento'
                                : 'Monto de descuento')
                            ->numeric()
                            ->required()
                            ->suffix(fn ($get) => $get('discount_type') === DiscountTypeEnum::PERCENTAGE->value ? '%' : null)
                            ->prefix(fn ($get) => $get('discount_type') === DiscountTypeEnum::FIXED_AMOUNT->value ? 'ARS' : null)
                            ->helperText(fn ($get) => $get('discount_type') === DiscountTypeEnum::PERCENTAGE->value
                                ? 'Ej: 10 para 10%'
                                : 'Monto en pesos'),

                        Select::make('scope')
                            ->label('Alcance del Cupón')
                            ->options(CouponScopeEnum::class)
                            ->default(CouponScopeEnum::GENERAL)
                            ->required()
                            ->live()
                            ->helperText('Selecciona productos/categorías en las pestañas de relación'),

                        TextInput::make('minimum_purchase')
                            ->label('Compra mínima')
                            ->numeric()
                            ->prefix('ARS')
                            ->helperText('Monto mínimo de compra para aplicar el cupón'),
                    ])->columns(2),

                Section::make('Límites de Uso')
                    ->schema([
                        TextInput::make('max_uses')
                            ->label('Límite total de usos')
                            ->numeric()
                            ->helperText('Dejar vacío para ilimitado'),

                        TextInput::make('max_uses_per_user')
                            ->label('Usos por usuario')
                            ->numeric()
                            ->helperText('Dejar vacío para ilimitado'),

                        TextInput::make('current_uses')
                            ->label('Usos actuales')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Section::make('Vigencia')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Fecha de inicio')
                            ->native(false),

                        DateTimePicker::make('expires_at')
                            ->label('Fecha de expiración')
                            ->native(false)
                            ->after('starts_at'),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
