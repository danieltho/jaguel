<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\ProductVariant;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items del Pedido';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('product_id'),

                Select::make('product_variant_id')
                    ->label('Variable de producto')
                    ->relationship('productVariant', 'sku', fn ($query) => $query->with(['product', 'color', 'size']))
                    ->getOptionLabelFromRecordUsing(fn (ProductVariant $record) =>
                        ($record->product?->name ? $record->product->name . ' — ' : '') .
                        $record->sku .
                        ($record->color ? ' - ' . $record->color->name : '') .
                        ($record->size ? ' - ' . $record->size->name : '')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $variant = ProductVariant::find($state);
                            if ($variant) {
                                $set('product_id', $variant->product_id);
                                $price = $variant->price_sales > 0
                                    ? $variant->price_sales
                                    : $variant->price_sold;
                                $set('unit_price', (int) round($price));
                            }
                        }
                    }),

                TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),

                TextInput::make('unit_price')
                    ->label('Precio Unitario')
                    ->numeric()
                    ->prefix('ARS')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->state(fn ($record) => $record->productVariant?->sku ?? $record->product?->sku)
                    ->placeholder('-'),
                TextColumn::make('name')
                    ->label('Producto')
                    ->state(fn ($record) => $record->productVariant?->product?->name ?? $record->product?->name)
                    ->placeholder('-'),
                TextColumn::make('color')
                    ->label('Color')
                    ->state(fn ($record) => $record->productVariant?->color?->name)
                    ->placeholder('-'),
                TextColumn::make('size')
                    ->label('Talla')
                    ->state(fn ($record) => $record->productVariant?->size?->name)
                    ->placeholder('-'),
                TextColumn::make('quantity')
                    ->label('Cantidad'),
                TextColumn::make('unit_price')
                    ->label('Precio Unit.')
                    ->money('ARS'),
            ])
            ->headerActions([
                CreateAction::make(),
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
