<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variantes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Precio')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('images')
                    ->disk('public')
                    ->directory('product/original')
                    ->panelLayout('grid')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->preserveFilenames()
                    ->downloadable()
                    ->responsiveImages()
                    ->imageEditor()
                    ->conversion('thumb')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('CLP')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
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
