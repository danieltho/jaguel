<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Coupon $record): string => CouponResource::getUrl('edit', ['record' => $record])),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->placeholder('Automático'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('discount_display')
                    ->label('Descuento')
                    ->getStateUsing(fn (Coupon $record) =>
                        $record->discount_type === DiscountTypeEnum::PERCENTAGE
                            ? $record->discount_value . '%'
                            : '$' . number_format($record->discount_value / 100, 2)
                    ),

                TextColumn::make('scope')
                    ->label('Alcance')
                    ->badge(),

                TextColumn::make('usage_display')
                    ->label('Usos')
                    ->getStateUsing(fn (Coupon $record) =>
                        $record->max_uses
                            ? "{$record->current_uses}/{$record->max_uses}"
                            : $record->current_uses
                    ),

                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin expiración'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(CouponTypeEnum::class),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
