<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductDetailVariantImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
    }

    public function test_variant_with_media_exposes_image_object_alongside_sku(): void
    {
        $product = Product::factory()->create();
        $variant = $this->makeVariant($product, 'SKU-RED');
        $variant->addMedia(UploadedFile::fake()->image('variant.jpg'))->toMediaCollection('variant');

        $this->get("/producto/{$product->slug}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('product.variants.0.sku', 'SKU-RED')
                ->has('product.variants.0.image', fn (Assert $image) => $image
                    ->has('id')
                    ->whereType('url', 'string')
                    ->has('thumb')
                )
        );
    }

    public function test_variant_without_media_has_null_image(): void
    {
        $product = Product::factory()->create();
        $this->makeVariant($product, 'SKU-NONE');

        $this->get("/producto/{$product->slug}")->assertInertia(
            fn (Assert $page) => $page
                ->where('product.variants.0.image', null)
        );
    }

    public function test_base_product_images_are_returned_for_variable_product(): void
    {
        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('base.jpg'))->toMediaCollection('default');
        // Producto variable: tiene variante, pero las imágenes base deben seguir llegando.
        $this->makeVariant($product, 'SKU-VAR');

        $this->get("/producto/{$product->slug}")->assertInertia(
            fn (Assert $page) => $page
                ->has('product.images', 1)
                ->has('product.images.0', fn (Assert $image) => $image
                    ->has('id')
                    ->whereType('url', 'string')
                    ->has('thumb')
                )
        );
    }

    private function makeVariant(Product $product, string $sku): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price_sold' => 10000,
            'price_sales' => 0,
            'stock' => 5,
            'sort_order' => 1,
            'color_id' => Color::create(['name' => 'Rojo', 'rgb_color' => 'FF0000'])->id,
            'size_id' => Size::create(['name' => 'M'])->id,
        ]);
    }
}
