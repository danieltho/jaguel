<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Orden')
                    ->schema([
                        Select::make('user_id')
                            ->label('Cliente')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $price = $product->price ?? 0;
                                        $set('subtotal', $price / 100);
                                        $set('discount_amount', 0);
                                        $set('price', $price / 100);
                                        $set('coupon_id', null);
                                        $set('coupon_code', null);
                                    }
                                }
                            }),
                    ])->columns(2),

                Section::make('Cupón de Descuento')
                    ->schema([
                        TextInput::make('coupon_code')
                            ->label('Código de Cupón')
                            ->placeholder('Ingresa el código del cupón')
                            ->dehydrated(false)
                            ->live(),

                        Actions::make([
                            Action::make('apply_coupon')
                                ->label('Aplicar Cupón')
                                ->icon('heroicon-o-ticket')
                                ->color('success')
                                ->action(function ($state, Set $set, $get) {
                                    $code = $get('coupon_code');
                                    $productId = $get('product_id');
                                    $userId = $get('user_id');
                                    $subtotal = ($get('subtotal') ?? 0) * 100;

                                    if (!$code || !$productId || !$userId) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('Debes seleccionar cliente, producto e ingresar un código de cupón')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $coupon = Coupon::where('code', $code)
                                        ->where('is_active', true)
                                        ->first();

                                    if (!$coupon) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Cupón inválido')
                                            ->body('El código de cupón no existe o no está activo')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $product = Product::find($productId);
                                    $user = \App\Models\User::find($userId);

                                    $couponService = new CouponService();
                                    $validation = $couponService->validateCoupon($coupon, $user, $product, $subtotal);

                                    if (!$validation['valid']) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Cupón no válido')
                                            ->body($validation['error'])
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $discount = $couponService->calculateDiscount($coupon, $subtotal);
                                    $finalPrice = $subtotal - $discount;

                                    $set('coupon_id', $coupon->id);
                                    $set('discount_amount', $discount / 100);
                                    $set('price', $finalPrice / 100);

                                    $discountText = $coupon->discount_type === DiscountTypeEnum::PERCENTAGE
                                        ? $coupon->discount_value . '%'
                                        : '$' . number_format($coupon->discount_value / 100, 2);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Cupón aplicado')
                                        ->body("Descuento de {$discountText} aplicado correctamente")
                                        ->success()
                                        ->send();
                                }),

                            Action::make('remove_coupon')
                                ->label('Quitar Cupón')
                                ->icon('heroicon-o-x-mark')
                                ->color('danger')
                                ->action(function (Set $set, $get) {
                                    $subtotal = $get('subtotal') ?? 0;
                                    $set('coupon_id', null);
                                    $set('coupon_code', null);
                                    $set('discount_amount', 0);
                                    $set('price', $subtotal);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Cupón removido')
                                        ->body('El cupón ha sido removido de la orden')
                                        ->info()
                                        ->send();
                                }),
                        ]),

                        Select::make('coupon_id')
                            ->label('Cupón Aplicado')
                            ->relationship('coupon', 'name')
                            ->disabled()
                            ->dehydrated(true),
                    ]),

                Section::make('Resumen de Precios')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('ARS')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('discount_amount')
                            ->label('Descuento')
                            ->numeric()
                            ->prefix('ARS')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),

                        TextInput::make('price')
                            ->label('Precio Final')
                            ->numeric()
                            ->prefix('ARS')
                            ->required()
                            ->live(),
                    ])->columns(3),
            ]);
    }
}
