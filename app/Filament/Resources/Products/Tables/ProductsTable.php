<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Título')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->state(fn (Product $record): int => $record->variants->sum('stock') ?: (int) ($record->stock ?? 0)),
                TextColumn::make('price_provider')
                    ->label('Proveedor')
                    ->money('ARS', locale: 'es_AR')
                    ->sortable(),
                TextColumn::make('price_sold')
                    ->label('Venta')
                    ->money('ARS', locale: 'es_AR')
                    ->sortable(),
                TextColumn::make('price_sales')
                    ->label('Promocional')
                    ->money('ARS', locale: 'es_AR')
                    ->sortable(),
                TextColumn::make('price_cost')
                    ->label('Costo')
                    ->money('ARS', locale: 'es_AR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('variants'))
            ->filters([
                Filter::make('name')
                    ->schema([
                        TextInput::make('name')->label('Título'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['name'] ?? null,
                        fn (Builder $q, string $v) => $q->where('name', 'like', "%{$v}%")
                    )),
                Filter::make('sku')
                    ->schema([
                        TextInput::make('sku')->label('SKU'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['sku'] ?? null,
                        fn (Builder $q, string $v) => $q->where('sku', 'like', "%{$v}%")
                    )),
                Filter::make('color_id')
                    ->schema([
                        Select::make('color_id')
                            ->label('Color')
                            ->options(fn () => Color::pluck('name', 'id'))
                            ->placeholder('Todos'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['color_id'] ?? null,
                        fn (Builder $q, $v) => $q->whereHas('variants', fn (Builder $vq) => $vq->where('color_id', $v))
                    )),
                Filter::make('size_id')
                    ->schema([
                        Select::make('size_id')
                            ->label('Talla')
                            ->options(fn () => Size::pluck('name', 'id'))
                            ->placeholder('Todas'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['size_id'] ?? null,
                        fn (Builder $q, $v) => $q->whereHas('variants', fn (Builder $vq) => $vq->where('size_id', $v))
                    )),
            ], layout: FiltersLayout::AboveContent)
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
