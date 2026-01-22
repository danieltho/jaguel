<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Models\Color;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    private static function calculateMargin(?float $cost, ?float $salePrice): ?float
    {
        if (!$cost || !$salePrice || $salePrice <= 0) {
            return null;
        }

        return round((($salePrice - $cost) / $salePrice) * 100, 2);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nombre y descripción')->schema([

                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),
                    RichEditor::make('description')
                        ->label('Descripción')
                        ,
                ])->columnSpanFull(),
                Section::make('Tipo de producto')->schema([
                    Radio::make('type')
                        ->hiddenLabel()
                            ->options(ProductTypeEnum::class)
                ])->columnSpanFull(),
                Section::make('Precios')->schema([
                    Grid::make()->columns(4)->schema([
                        TextInput::make('price_sold')
                            ->label('Precio Venta')
                            ->postfix('$')
                            ->numeric(),
                        TextInput::make('price_sales')
                            ->label('Precio Promocional')
                            ->postfix('$')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set, $get) => $set('profit_margin', self::calculateMargin($get('price_cost'), $state))),
                        TextInput::make('price_cost')
                            ->label('Precio Costo')
                            ->postfix('$')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set, $get) => $set('profit_margin', self::calculateMargin($state, $get('price_sales')))),
                        TextInput::make('profit_margin')
                            ->label('Margen de ganancia')
                            ->dehydrated(false)
                            ->postfix('%')
                            ->disabled(),
                        TextInput::make('price_provider')
                            ->label('Precio proveedor')
                            ->postfix('$')
                            ->numeric(),
                    ]),
                ])->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('files')
                    ->label('Imágenes')
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
                    ->columnSpanFull(),

                Section::make('Peso y dimensiones')->schema([
                    Grid::make()->columns(4)->schema([
                        TextInput::make('dimension_weight')
                            ->label('Peso')
                            ->postfix('Kg')
                            ->placeholder('0.14')
                            ->numeric(),
                        TextInput::make('dimension_length')
                            ->label('Profundidad')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->numeric(),
                        TextInput::make('dimension_height')
                            ->label('Ancho')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->numeric(),
                        TextInput::make('dimension_with')
                            ->label('Alto')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->numeric(),
                    ]),
                ])->columnSpanFull(),


                Section::make('Inventario')->schema([
                    Radio::make('inventario_type')
                        ->hiddenLabel()
                        ->dehydrated(false)
                        ->options(ProductStatusEnum::class)
                        ->default(ProductStatusEnum::OUT_STOCK->value)
                        ->afterStateHydrated(function ($state, $set, $get) {
                            $stock = $get('stock');
                            $set('inventario_type', is_null($stock) ? ProductStatusEnum::OUT_STOCK->value : ProductStatusEnum::IN_STOCK->value);
                        })
                        ->live(),
                    TextInput::make('stock')
                        ->label('Cantidad')
                        ->numeric()
                        ->hidden(fn ($get) => $get('inventario_type') !== ProductStatusEnum::IN_STOCK->value),
                ])->columnSpanFull(),

                Section::make('Variantes')->schema([
                    SchemaActions::make([
                        Action::make('generate_variants')
                            ->label('Generar variantes')
                            ->icon('heroicon-o-plus-circle')
                            ->modalHeading('Seleccionar variantes')
                            ->modalDescription('Seleccioná los colores y/o talles para generar las variantes del producto.')
                            ->modalSubmitActionLabel('Generar')
                            ->schema([
                                CheckboxList::make('selected_colors')
                                    ->label('Colores')
                                    ->options(Color::pluck('name', 'id'))
                                    ->columns(3),
                                CheckboxList::make('selected_sizes')
                                    ->label('Talles')
                                    ->options(Size::pluck('name', 'id'))
                                    ->columns(4),
                            ])
                            ->action(function (array $data, $get, $set) {
                                $colors = $data['selected_colors'] ?? [];
                                $sizes = $data['selected_sizes'] ?? [];
                                $currentVariants = $get('variants') ?? [];
                                $priceSold = $get('price_sold');
                                $priceSales = $get('price_sales');
                                $priceCost = $get('price_cost');
                                $priceProvider = $get('price_provider');
                                $stock = $get('stock') ?? 0;

                                $newVariants = [];

                                if (!empty($colors) && !empty($sizes)) {
                                    foreach ($colors as $colorId) {
                                        foreach ($sizes as $sizeId) {
                                            $exists = collect($currentVariants)->contains(fn ($v) =>
                                                ($v['color_id'] ?? null) == $colorId &&
                                                ($v['size_id'] ?? null) == $sizeId
                                            );
                                            if (!$exists) {
                                                $newVariants[] = [
                                                    'color_id' => $colorId,
                                                    'size_id' => $sizeId,
                                                    'price_sold' => $priceSold,
                                                    'price_sales' => $priceSales,
                                                    'price_cost' => $priceCost,
                                                    'price_provider' => $priceProvider,
                                                    'stock' => $stock,
                                                ];
                                            }
                                        }
                                    }
                                } elseif (!empty($colors)) {
                                    foreach ($colors as $colorId) {
                                        $exists = collect($currentVariants)->contains(fn ($v) =>
                                            ($v['color_id'] ?? null) == $colorId &&
                                            empty($v['size_id'])
                                        );
                                        if (!$exists) {
                                            $newVariants[] = [
                                                'color_id' => $colorId,
                                                'size_id' => null,
                                                'price_sold' => $priceSold,
                                                'price_sales' => $priceSales,
                                                'price_cost' => $priceCost,
                                                'price_provider' => $priceProvider,
                                                'stock' => $stock,
                                            ];
                                        }
                                    }
                                } elseif (!empty($sizes)) {
                                    foreach ($sizes as $sizeId) {
                                        $exists = collect($currentVariants)->contains(fn ($v) =>
                                            empty($v['color_id']) &&
                                            ($v['size_id'] ?? null) == $sizeId
                                        );
                                        if (!$exists) {
                                            $newVariants[] = [
                                                'color_id' => null,
                                                'size_id' => $sizeId,
                                                'price_sold' => $priceSold,
                                                'price_sales' => $priceSales,
                                                'price_cost' => $priceCost,
                                                'price_provider' => $priceProvider,
                                                'stock' => $stock,
                                            ];
                                        }
                                    }
                                }

                                $set('variants', array_merge($currentVariants, $newVariants));
                            }),
                    ]),
                    Repeater::make('variants')
                        ->relationship()
                        ->hiddenLabel()
                        ->addable(false)
                        ->schema([
                            Grid::make()->columns(5)->schema([
                                Select::make('color_id')
                                    ->label('Color')
                                    ->options(Color::pluck('name', 'id'))
                                    ->disabled(fn ($get) => filled($get('color_id')))
                                    ->dehydrated()
                                    ->placeholder('Sin color'),
                                Select::make('size_id')
                                    ->label('Talle')
                                    ->options(Size::pluck('name', 'id'))
                                    ->disabled(fn ($get) => filled($get('size_id')))
                                    ->dehydrated()
                                    ->placeholder('Sin talle'),
                                TextInput::make('price_sold')
                                    ->label('Precio Venta')
                                    ->numeric()
                                    ->postfix('$'),
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric(),
                                SpatieMediaLibraryFileUpload::make('variant_image')
                                    ->label('Imagen')
                                    ->collection('variant')
                                    ->disk('public')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                        ])
                        ->extraItemActions([
                            Action::make('edit_variant')
                                ->label('Editar')
                                ->icon('heroicon-o-pencil-square')
                                ->modalHeading( 'Editar variante: ')
                                ->fillForm(fn (array $arguments, $get) => [
                                    'price_sold' => $get("variants.{$arguments['item']}.price_sold"),
                                    'price_sales' => $get("variants.{$arguments['item']}.price_sales"),
                                    'price_cost' => $get("variants.{$arguments['item']}.price_cost"),
                                    'price_provider' => $get("variants.{$arguments['item']}.price_provider"),
                                    'stock' => $get("variants.{$arguments['item']}.stock"),
                                    'dimension_weight' => $get("variants.{$arguments['item']}.dimension_weight"),
                                    'dimension_height' => $get("variants.{$arguments['item']}.dimension_height"),
                                    'dimension_width' => $get("variants.{$arguments['item']}.dimension_width"),
                                    'dimension_length' => $get("variants.{$arguments['item']}.dimension_length"),
                                ])
                                ->schema([
                                    Section::make('Precios')->schema([
                                        Grid::make()->columns(4)->schema([
                                            TextInput::make('price_sold')
                                                ->label('Precio Venta')
                                                ->postfix('$')
                                                ->numeric(),
                                            TextInput::make('price_sales')
                                                ->label('Precio Promocional')
                                                ->postfix('$')
                                                ->numeric(),
                                            TextInput::make('price_cost')
                                                ->label('Precio Costo')
                                                ->postfix('$')
                                                ->numeric(),
                                            TextInput::make('price_provider')
                                                ->label('Precio Proveedor')
                                                ->postfix('$')
                                                ->numeric(),
                                        ]),
                                    ]),
                                    Section::make('Inventario')->schema([
                                        TextInput::make('stock')
                                            ->label('Stock')
                                            ->numeric(),
                                    ]),
                                    Section::make('Peso y dimensiones')->schema([
                                        Grid::make()->columns(4)->schema([
                                            TextInput::make('dimension_weight')
                                                ->label('Peso')
                                                ->postfix('Kg')
                                                ->numeric(),
                                            TextInput::make('dimension_length')
                                                ->label('Profundidad')
                                                ->postfix('cm')
                                                ->numeric(),
                                            TextInput::make('dimension_height')
                                                ->label('Ancho')
                                                ->postfix('cm')
                                                ->numeric(),
                                            TextInput::make('dimension_width')
                                                ->label('Alto')
                                                ->postfix('cm')
                                                ->numeric(),
                                        ]),
                                    ]),
                                ])
                                ->action(function (array $data, array $arguments, $get, $set) {
                                    $index = $arguments['item'];
                                    $set("variants.{$index}.price_sold", $data['price_sold']);
                                    $set("variants.{$index}.price_sales", $data['price_sales']);
                                    $set("variants.{$index}.price_cost", $data['price_cost']);
                                    $set("variants.{$index}.price_provider", $data['price_provider']);
                                    $set("variants.{$index}.stock", $data['stock']);
                                    $set("variants.{$index}.dimension_weight", $data['dimension_weight']);
                                    $set("variants.{$index}.dimension_height", $data['dimension_height']);
                                    $set("variants.{$index}.dimension_width", $data['dimension_width']);
                                    $set("variants.{$index}.dimension_length", $data['dimension_length']);
                                }),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string =>
                            collect([
                                isset($state['color_id']) ? Color::find($state['color_id'])?->name : null,
                                isset($state['size_id']) ? Size::find($state['size_id'])?->name : null,
                            ])->filter()->implode(' - ') ?: 'Variante'
                        ),
                ])
                    ->columnSpanFull(),

            ]);
    }
}
