<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\ProductVariant;
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
                Select::make('product_variant_id')
                    ->label('Variante de Producto')
                    ->relationship('productVariant')
                    ->getOptionLabelFromRecordUsing(fn (ProductVariant $record) =>
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
                                $set('unit_price', $variant->price);
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
                TextColumn::make('productVariant.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('productVariant.product.name')
                    ->label('Producto'),
                TextColumn::make('productVariant.color.name')
                    ->label('Color'),
                TextColumn::make('productVariant.size.name')
                    ->label('Talla'),
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
