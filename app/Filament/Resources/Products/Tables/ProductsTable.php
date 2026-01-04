<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')->label('Name')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit',['record'=> $record]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')->label('Price')
                    ->money('ARS', 100)
                    ->alignEnd()
                    //->formatS tateUsing(fn (int $state): float =>  $state /100)
                    ->sortable(),
                SelectColumn::make('status')
                    ->options(ProductStatusEnum::class),
                CheckboxColumn::make('is_active'),
                //ToggleColumn::make('is_active'),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('tags.name')->badge(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    //->date('d/m/Y')
                    ->since()
                ,
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                    ])->query(
                        function (Builder $query, array $data): Builder {
                            return $query->when($data['created_from'], function (Builder $query, $date) {
                                return $query->where('created_at', '>=', $date);
                            });
                        }
                    ),
                Filter::make('created_until')
                    ->schema([
                        DatePicker::make('created_until'),
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
