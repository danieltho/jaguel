<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                RichEditor::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Toggle::make('is_custom')
                    ->label('Personalizado')
                    ->default(false),
                Toggle::make('is_simple')
                    ->label('Simple')
                    ->default(false)
                    ->live(),
                TextInput::make('price')
                    ->label('Precio')
                    ->prefix('ARS')
                    ->numeric()
                    ->visible(fn ($get) => $get('is_simple')),
                Toggle::make('is_featured')
                    ->label('Destacado')
                    ->default(false),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
                SpatieMediaLibraryFileUpload::make('files')
                    ->label('Imágenes')
                    ->columnSpanFull()
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
}
