<?php

namespace App\Filament\Resources\ProductVariants\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ProductVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->formatStateUsing(fn () => ''),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
                TextColumn::make('price_sold')
                    ->label('Precio Venta')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('price_sales')
                    ->label('Precio Promocional')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('size.name')
                    ->label('Talle')
                    ->sortable()
                    ->placeholder('Sin talle'),
                TextColumn::make('color.name')
                    ->label('Color')
                    ->sortable()
                    ->placeholder('Sin color'),
            ])
            ->groups([
                Group::make('product.name')
                    ->label('Producto')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('product.name')
            ->filters([
                SelectFilter::make('color_id')
                    ->label('Color')
                    ->relationship('color', 'name'),
                SelectFilter::make('size_id')
                    ->label('Talle')
                    ->relationship('size', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (ProductVariant $record): string => ProductResource::getUrl('edit', ['record' => $record->product_id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
