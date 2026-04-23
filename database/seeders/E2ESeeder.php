<?php

namespace Database\Seeders;

use App\Enums\ProductTypeEnum;
use App\Models\Category;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fixtures required by the jaguel-e2e Playwright suite.
 * Idempotent — safe to run multiple times without duplicating rows.
 * Slugs and emails used here MUST match the defaults in jaguel-e2e/.env.example.
 */
class E2ESeeder extends Seeder
{
    private const PRODUCT_SLUG = 'e2e-mate-clasico';
    private const PRODUCT_NAME = 'E2E Mate Clásico';
    private const PRODUCT_CATEGORY_SLUG = 'mates';
    private const CUSTOMER_EMAIL = 'e2e@jaguel.test';
    private const CUSTOMER_PASSWORD = 'Password123!';
    private const ASSETS_PATH = 'seeders/assets/products';

    public function run(): void
    {
        $this->seedCustomer();
        $this->seedProduct();
    }

    private function seedCustomer(): void
    {
        Customer::updateOrCreate(
            ['email' => self::CUSTOMER_EMAIL],
            [
                'firstname' => 'E2E',
                'lastname' => 'Tester',
                'password' => Hash::make(self::CUSTOMER_PASSWORD),
                'country_iso' => 'AR',
                'receive_offers' => false,
            ]
        );
    }

    private function seedProduct(): void
    {
        $category = Category::where('slug', self::PRODUCT_CATEGORY_SLUG)->first();
        if (! $category) {
            $this->command?->error(sprintf(
                "E2ESeeder: category '%s' not found. Run CategoryWithGroupSeeder first.",
                self::PRODUCT_CATEGORY_SLUG
            ));
            return;
        }

        $product = Product::where('slug', self::PRODUCT_SLUG)->first();

        if (! $product) {
            $product = Product::create([
                'name' => self::PRODUCT_NAME,
                'slug' => self::PRODUCT_SLUG,
                'description' => 'Producto de referencia para la suite E2E. No borrar.',
                'category_id' => $category->id,
                'type' => ProductTypeEnum::FISICO,
                'price_sold' => 45000,
                'price_sales' => 0,
                'price_provider' => 22500,
                'price_cost' => 27000,
                'is_active' => true,
            ]);

            DB::table('products')->where('id', $product->id)->update(['slug' => self::PRODUCT_SLUG]);
            $product->refresh();
        } else {
            $product->fill([
                'name' => self::PRODUCT_NAME,
                'description' => 'Producto de referencia para la suite E2E. No borrar.',
                'category_id' => $category->id,
                'price_sold' => 45000,
                'is_active' => true,
            ])->saveQuietly();

            DB::table('products')->where('id', $product->id)->update(['slug' => self::PRODUCT_SLUG]);
        }

        $this->seedVariants($product);
        $this->attachImage($product);
    }

    private function seedVariants(Product $product): void
    {
        $sizes = Size::all()->keyBy('name');
        $colors = Color::all()->keyBy('name');

        if ($sizes->isEmpty() || $colors->isEmpty()) {
            $this->command?->warn('E2ESeeder: no sizes or colors found, skipping variants.');
            return;
        }

        $plan = [
            ['size' => $sizes->first()->name, 'color' => $colors->first()->name, 'stock' => 10],
            ['size' => $sizes->first()->name, 'color' => $colors->skip(1)->first()?->name, 'stock' => 5],
            ['size' => ($sizes->skip(1)->first()?->name) ?? $sizes->first()->name, 'color' => $colors->first()->name, 'stock' => 0],
        ];

        foreach ($plan as $index => $spec) {
            if (! $spec['color']) {
                continue;
            }

            $size = $sizes[$spec['size']] ?? null;
            $color = $colors[$spec['color']] ?? null;
            $sku = sprintf('E2E-MT-%s-%s', $spec['size'], $spec['color']);

            ProductVariant::updateOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'size_id' => $size?->id,
                    'color_id' => $color?->id,
                    'price_sold' => 45000,
                    'price_sales' => 0,
                    'price_provider' => 22500,
                    'price_cost' => 27000,
                    'stock' => $spec['stock'],
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function attachImage(Product $product): void
    {
        if ($product->getFirstMedia('default')) {
            return;
        }

        $path = database_path(self::ASSETS_PATH . '/test-simple.png');
        if (! file_exists($path)) {
            $this->command?->warn("E2ESeeder: image not found at {$path}, product will render without media.");
            return;
        }

        $product->addMedia($path)->preservingOriginal()->toMediaCollection('default');
    }
}
