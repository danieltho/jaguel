<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                ->searchable()
                ->sortable(),
                TextColumn::make('price')->label('Price')
                    ->money('ARS', 100)
                    //->formatS tateUsing(fn (int $state): float =>  $state /100)
                ->sortable(),
                TextColumn::make('status')->label('Status')
                ,
                TextColumn::make('category.name')
            ])
            ->filters([
                //
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
