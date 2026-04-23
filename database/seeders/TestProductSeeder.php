<?php

namespace Database\Seeders;

use App\Enums\ProductTypeEnum;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestProductSeeder extends Seeder
{
    private const ASSETS_PATH = 'seeders/assets/products';
    private const IVA_RATE = 1.21;

    public function run(): void
    {
        $this->createSimpleProduct();
        $this->createPersonalizableProduct();
        $this->createSingleVariantProduct();
        $this->createCompositeVariantProduct();
    }

    private function createSimpleProduct(): void
    {
        $product = $this->createProduct(
            name: 'Producto Test Simple',
            sku: 'PTS0001',
            description: 'Producto de prueba sin variantes ni personalización. Flujo simple de compra.',
            categorySlug: 'llaveros',
            priceSold: 15000,
        );

        $this->attachImage($product, 'test-simple.png');
    }

    private function createPersonalizableProduct(): void
    {
        $product = $this->createProduct(
            name: 'Producto Test Personalizado',
            sku: 'PTP0001',
            description: 'Producto de prueba que permite elegir si se personaliza (SI/NO) al agregarlo al carrito.',
            categorySlug: 'mates',
            priceSold: 55000,
        );

        $this->attachImage($product, 'test-personalizado.png');
    }

    private function createSingleVariantProduct(): void
    {
        $product = $this->createProduct(
            name: 'Producto Test Variante',
            sku: 'PTV0001',
            description: 'Producto de prueba con una sola dimensión de variante: Talle.',
            categorySlug: 'indumentaria',
            priceSold: 48000,
            priceSales: 42000,
        );

        $this->attachImage($product, 'test-variante.png');

        $sizes = Size::all();
        foreach ($sizes as $index => $size) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => "PTV-{$size->name}",
                'size_id' => $size->id,
                'color_id' => null,
                'price_sold' => $product->price_sold,
                'price_sales' => 0,
                'price_provider' => (int) ($product->price_sold * 0.5),
                'price_cost' => (int) ($product->price_sold * 0.6),
                'stock' => $size->name === 'L' ? 0 : 10,
                'sort_order' => $index,
            ]);
        }
    }

    private function createCompositeVariantProduct(): void
    {
        $product = $this->createProduct(
            name: 'Producto Test Variante 2',
            sku: 'PTV20001',
            description: 'Producto de prueba con dos dimensiones de variante: Talle + Color.',
            categorySlug: 'indumentaria',
            priceSold: 52000,
        );

        $this->attachImage($product, 'test-variante.png');

        $sizes = Size::all();
        $colors = Color::all();
        $sortOrder = 0;

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => "PTV2-{$size->name}-{$color->name}",
                    'size_id' => $size->id,
                    'color_id' => $color->id,
                    'price_sold' => $product->price_sold,
                    'price_sales' => 0,
                    'price_provider' => (int) ($product->price_sold * 0.5),
                    'price_cost' => (int) ($product->price_sold * 0.6),
                    'stock' => ($size->name === 'L' && $color->name === 'Amarillo') ? 0 : 8,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    private function createProduct(
        string $name,
        string $sku,
        string $description,
        string $categorySlug,
        int $priceSold,
        int $priceSales = 0,
    ): Product {
        $category = Category::where('slug', $categorySlug)->first();

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => $sku,
            'description' => $description,
            'category_id' => $category?->id,
            'type' => ProductTypeEnum::FISICO,
            'price_sold' => $priceSold,
            'price_without_tax' => (int) round($priceSold / self::IVA_RATE),
            'price_sales' => $priceSales,
            'price_provider' => (int) ($priceSold * 0.5),
            'price_cost' => (int) ($priceSold * 0.6),
            'is_active' => true,
        ]);
    }

    private function attachImage(Product $product, string $filename): void
    {
        $path = database_path(self::ASSETS_PATH . '/' . $filename);

        if (! file_exists($path)) {
            $this->command?->warn("Imagen no encontrada: {$path}");
            return;
        }

        $product
            ->addMedia($path)
            ->preservingOriginal()
            ->toMediaCollection('default');
    }
}
