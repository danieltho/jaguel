<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Models\Color;
use Filament\Forms\Components\Select;
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

    protected static ?string $title = 'Product variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Precio')
                    ->numeric()
                    ->prefix('ARS'),
                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
                Select::make('color_id')
                    ->label('Color')
                    ->relationship('color', 'name')
                    ->preload(),
                Select::make('size_id')
                    ->label('Talla')
                    ->relationship('size', 'name')
                    ->preload(),
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
                    ->conversion('thumb'),
                \Filament\Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS', 100)
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['price'])) {
            $data['price'] = $data['price'] / 100;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['price'])) {
            $data['price'] = $data['price'] * 100;
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['price'])) {
            $data['price'] = $data['price'] * 100;
        }

        return $data;
    }

    public function isReadOnly(): bool
    {
        return $this->getOwnerRecord()->is_simple;
    }
}
