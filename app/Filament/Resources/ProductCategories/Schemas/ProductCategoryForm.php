<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
