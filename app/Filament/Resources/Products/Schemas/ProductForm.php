<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
                Hidden::make('type')->default(ProductTypeEnum::FISICO->value),

                Section::make('Producto general')->schema([
                    TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255),
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),
                    RichEditor::make('description')
                        ->label('Descripción')
                        ->toolbarButtons(['bold']),
                ])->columnSpanFull(),

                Section::make('Precios')->schema([
                    Grid::make()->columns(4)->schema([
                        TextInput::make('price_cost')
                            ->label('Precio Costo')
                            ->postfix('$')
                            ->rule('numeric')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => $set('profit_margin', self::calculateMargin($state, $get('price_sales')))),
                        TextInput::make('price_provider')
                            ->label('Precio Proveedor')
                            ->postfix('$')
                            ->rule('numeric'),
                        TextInput::make('price_sold')
                            ->label('Precio Venta')
                            ->postfix('$')
                            ->rule('numeric'),
                        TextInput::make('price_without_tax')
                            ->label('Precio sin impuesto')
                            ->postfix('$')
                            ->rule('numeric'),
                        TextInput::make('price_sales')
                            ->label('Precio Promocional')
                            ->postfix('$')
                            ->rule('numeric')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => $set('profit_margin', self::calculateMargin($get('price_cost'), $state))),
                        TextInput::make('profit_margin')
                            ->label('Margen de ganancia')
                            ->dehydrated(false)
                            ->postfix('%')
                            ->rule('numeric')
                            ->disabled()
                            ->afterStateHydrated(fn ($set, $get) => $set('profit_margin', self::calculateMargin($get('price_cost'), $get('price_sales')))),
                    ]),
                ])->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('files')
                    ->label('Imágenes')
                    ->collection('default')
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
                            ->rule('numeric'),
                        TextInput::make('dimension_length')
                            ->label('Profundidad')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->rule('numeric'),
                        TextInput::make('dimension_width')
                            ->label('Ancho')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->rule('numeric'),
                        TextInput::make('dimension_height')
                            ->label('Alto')
                            ->postfix('cm')
                            ->placeholder('30')
                            ->rule('numeric'),
                    ]),
                ])->columnSpanFull(),

                Section::make('Inventario')->schema([
                    Select::make('inventario_type')
                        ->label('Tipo de inventario')
                        ->dehydrated(false)
                        ->options(ProductStatusEnum::class)
                        ->default(ProductStatusEnum::IN_STOCK->value)
                        ->selectablePlaceholder(false)
                        ->afterStateHydrated(function ($state, $set, $get) {
                            $stock = $get('stock');
                            $set('inventario_type', is_null($stock)
                                ? ProductStatusEnum::OUT_STOCK->value
                                : ProductStatusEnum::IN_STOCK->value);
                        })
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state === ProductStatusEnum::OUT_STOCK->value) {
                                $set('stock', null);
                            }
                        })
                        ->live(),
                    TextInput::make('stock')
                        ->label('Cantidad')
                        ->rule('numeric')
                        ->hidden(fn ($get) => $get('inventario_type') === ProductStatusEnum::OUT_STOCK->value),
                ])
                    ->columnSpanFull()
                    ->hidden(fn ($get) => !empty($get('variants'))),

                Section::make('Variantes')->schema([
                    SchemaActions::make([
                        Action::make('generate_variants')
                            ->label('Generar variantes')
                            ->icon('heroicon-o-plus-circle')
                            ->modalHeading('Seleccionar variantes')
                            ->modalDescription('Seleccioná los colores y/o talles para generar las variantes del producto.')
                            ->modalSubmitActionLabel('Generar')
                            ->fillForm(function ($get) {
                                $currentVariants = $get('variants') ?? [];

                                $selectedColors = collect($currentVariants)
                                    ->pluck('color_id')
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->toArray();
                                $selectedSizes = collect($currentVariants)
                                    ->pluck('size_id')
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->toArray();

                                $colorMap = $selectedColors
                                    ? Color::whereIn('id', $selectedColors)->pluck('name', 'id')
                                    : collect();
                                $sizeMap = $selectedSizes
                                    ? Size::whereIn('id', $selectedSizes)->pluck('name', 'id')
                                    : collect();

                                $existingCombos = collect($currentVariants)
                                    ->map(function ($v) use ($colorMap, $sizeMap) {
                                        $color = isset($v['color_id']) ? ($colorMap[$v['color_id']] ?? null) : null;
                                        $size = isset($v['size_id']) ? ($sizeMap[$v['size_id']] ?? null) : null;

                                        return collect([$color, $size])->filter()->implode(' / ');
                                    })
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->toArray();

                                return [
                                    'selected_colors' => $selectedColors,
                                    'selected_sizes' => $selectedSizes,
                                    'existing_combos' => $existingCombos,
                                ];
                            })
                            ->schema([
                                Placeholder::make('existing_variants')
                                    ->label('Combinaciones ya creadas')
                                    ->content(function ($get) {
                                        $combos = $get('existing_combos') ?? [];
                                        if (empty($combos)) {
                                            return new HtmlString('<span class="text-sm text-gray-500">Aún no hay variantes. Seleccioná colores y/o talles para crear las primeras.</span>');
                                        }
                                        $badges = collect($combos)
                                            ->map(fn (string $combo) => '<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-200">'.e($combo).'</span>')
                                            ->implode(' ');

                                        return new HtmlString('<div class="flex flex-wrap gap-2">'.$badges.'</div>');
                                    }),
                                Hidden::make('existing_combos'),
                                CheckboxList::make('selected_colors')
                                    ->label('Colores')
                                    ->options(Color::pluck('name', 'id'))
                                    ->columns(3),
                                CheckboxList::make('selected_sizes')
                                    ->label('Talles')
                                    ->options(Size::pluck('name', 'id'))
                                    ->columns(4),
                            ])
                            ->action(function (array $data, $get, $set, ?Product $record) {
                                $colors = $data['selected_colors'] ?? [];
                                $sizes = $data['selected_sizes'] ?? [];
                                $currentVariants = $get('variants') ?? [];
                                $priceSold = $get('price_sold');
                                $priceSales = $get('price_sales');
                                $priceCost = $get('price_cost');
                                $priceProvider = $get('price_provider');
                                $stock = $get('stock') ?? 0;

                                $title = (string) ($get('name') ?? '');
                                $letters = preg_replace('/[^A-Z]/', '', strtoupper(Str::ascii($title)));
                                $prefix = str_pad(substr($letters, 0, 4), 4, 'X');
                                $idPart = $record?->id ? '00'.$record->id : '';

                                $colorMap = $colors ? Color::whereIn('id', $colors)->pluck('name', 'id') : collect();
                                $sizeMap = $sizes ? Size::whereIn('id', $sizes)->pluck('name', 'id') : collect();

                                $normalize = fn (?string $value) => $value
                                    ? preg_replace('/[^A-Z0-9]/', '', strtoupper(Str::ascii($value)))
                                    : '';

                                $newVariants = [];

                                $makeVariant = function ($colorId, $sizeId) use ($prefix, $idPart, $colorMap, $sizeMap, $normalize, $priceSold, $priceSales, $priceCost, $priceProvider, $stock) {
                                    $suffix = $normalize($colorMap[$colorId] ?? null).$normalize($sizeMap[$sizeId] ?? null);

                                    return [
                                        'sku' => $prefix.$idPart.($suffix !== '' ? '-'.$suffix : ''),
                                        'color_id' => $colorId,
                                        'size_id' => $sizeId,
                                        'price_sold' => $priceSold,
                                        'price_sales' => $priceSales,
                                        'price_cost' => $priceCost,
                                        'price_provider' => $priceProvider,
                                        'stock' => $stock,
                                    ];
                                };

                                if (!empty($colors) && !empty($sizes)) {
                                    foreach ($colors as $colorId) {
                                        foreach ($sizes as $sizeId) {
                                            $exists = collect($currentVariants)->contains(fn ($v) =>
                                                ($v['color_id'] ?? null) == $colorId &&
                                                ($v['size_id'] ?? null) == $sizeId
                                            );
                                            if (!$exists) {
                                                $newVariants[] = $makeVariant($colorId, $sizeId);
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
                                            $newVariants[] = $makeVariant($colorId, null);
                                        }
                                    }
                                } elseif (!empty($sizes)) {
                                    foreach ($sizes as $sizeId) {
                                        $exists = collect($currentVariants)->contains(fn ($v) =>
                                            empty($v['color_id']) &&
                                            ($v['size_id'] ?? null) == $sizeId
                                        );
                                        if (!$exists) {
                                            $newVariants[] = $makeVariant(null, $sizeId);
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
                            Grid::make()->columns(6)->schema([
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(255),
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
                                    ->rule('numeric')
                                    ->postfix('$'),
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->rule('numeric'),
                                SpatieMediaLibraryFileUpload::make('variant_image')
                                    ->label('Imagen')
                                    ->collection('variant')
                                    ->disk('public')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                            Hidden::make('price_sales'),
                            Hidden::make('price_cost'),
                            Hidden::make('price_provider'),
                            Hidden::make('dimension_weight'),
                            Hidden::make('dimension_height'),
                            Hidden::make('dimension_width'),
                            Hidden::make('dimension_length'),
                        ])
                        ->extraItemActions([
                            Action::make('edit_variant')
                                ->label('Editar')
                                ->icon('heroicon-o-pencil-square')
                                ->modalHeading('Editar variante')
                                ->fillForm(fn (array $arguments, $get) => [
                                    'sku' => $get("variants.{$arguments['item']}.sku"),
                                    'color_id' => $get("variants.{$arguments['item']}.color_id"),
                                    'size_id' => $get("variants.{$arguments['item']}.size_id"),
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
                                    Section::make('Identificación')->schema([
                                        Grid::make()->columns(3)->schema([
                                            TextInput::make('sku')
                                                ->label('SKU')
                                                ->maxLength(255),
                                            Select::make('color_id')
                                                ->label('Color')
                                                ->options(Color::pluck('name', 'id'))
                                                ->placeholder('Sin color'),
                                            Select::make('size_id')
                                                ->label('Talle')
                                                ->options(Size::pluck('name', 'id'))
                                                ->placeholder('Sin talle'),
                                        ]),
                                    ]),
                                    Section::make('Precios')->schema([
                                        Grid::make()->columns(4)->schema([
                                            TextInput::make('price_sold')
                                                ->label('Precio Venta')
                                                ->postfix('$')
                                                ->rule('numeric'),
                                            TextInput::make('price_sales')
                                                ->label('Precio Promocional')
                                                ->postfix('$')
                                                ->rule('numeric'),
                                            TextInput::make('price_cost')
                                                ->label('Precio Costo')
                                                ->postfix('$')
                                                ->rule('numeric'),
                                            TextInput::make('price_provider')
                                                ->label('Precio Proveedor')
                                                ->postfix('$')
                                                ->rule('numeric'),
                                        ]),
                                    ]),
                                    Section::make('Inventario')->schema([
                                        TextInput::make('stock')
                                            ->label('Stock')
                                            ->rule('numeric'),
                                    ]),
                                    Section::make('Peso y dimensiones')->schema([
                                        Grid::make()->columns(4)->schema([
                                            TextInput::make('dimension_weight')
                                                ->label('Peso')
                                                ->postfix('Kg')
                                                ->rule('numeric'),
                                            TextInput::make('dimension_length')
                                                ->label('Profundidad')
                                                ->postfix('cm')
                                                ->rule('numeric'),
                                            TextInput::make('dimension_width')
                                                ->label('Ancho')
                                                ->postfix('cm')
                                                ->rule('numeric'),
                                            TextInput::make('dimension_height')
                                                ->label('Alto')
                                                ->postfix('cm')
                                                ->rule('numeric'),
                                        ]),
                                    ]),
                                ])
                                ->action(function (array $data, array $arguments, $get, $set) {
                                    $index = $arguments['item'];
                                    $set("variants.{$index}.sku", $data['sku']);
                                    $set("variants.{$index}.color_id", $data['color_id']);
                                    $set("variants.{$index}.size_id", $data['size_id']);
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
                        ->itemLabel(function (array $state, $get): ?string {
                            $label = collect([
                                isset($state['color_id']) ? Color::find($state['color_id'])?->name : null,
                                isset($state['size_id']) ? Size::find($state['size_id'])?->name : null,
                            ])->filter()->implode(' - ') ?: 'Variante';

                            $allVariants = $get('../../variants') ?? [];
                            $currentColorId = $state['color_id'] ?? null;
                            $currentSizeId = $state['size_id'] ?? null;

                            $duplicateCount = collect($allVariants)->filter(function ($variant) use ($currentColorId, $currentSizeId) {
                                return ($variant['color_id'] ?? null) == $currentColorId
                                    && ($variant['size_id'] ?? null) == $currentSizeId;
                            })->count();

                            if ($duplicateCount > 1) {
                                return "[DUPLICADO] {$label}";
                            }

                            return $label;
                        }),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
