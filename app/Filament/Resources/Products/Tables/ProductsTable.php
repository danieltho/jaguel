<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Producto')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variants_count')
                    ->label('Stock')
                    ->counts('variants')
                    ->sortable(),
                TextColumn::make('media_count')
                    ->label('Imágenes')
                    ->counts('media')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Desde'),
                    ])->query(
                        function (Builder $query, array $data): Builder {
                            return $query->when($data['created_from'], function (Builder $query, $date) {
                                return $query->where('created_at', '>=', $date);
                            });
                        }
                    ),
                Filter::make('created_until')
                    ->schema([
                        DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])->query(
                        function (Builder $query, array $data): Builder {
                            return $query->when($data['created_until'], function (Builder $query, $date) {
                                return $query->where('created_at', '<=', $date);
                            });
                        }
                    )
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
