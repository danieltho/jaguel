<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CouponService;
use App\Support\MediaUrl;
use Inertia\Inertia;
use Inertia\Response;

class ProductDetailController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function show(string $slug, ?string $variantSku = null): Response
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category.categoryGroup',
                'categoryGroup',
                'variants' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'variants.color',
                'variants.size',
                'variants.media',
                'media',
                'tags',
            ])
            ->firstOrFail();

        $relatedProducts = Product::where('is_active', true)
            ->where('category_group_id', $product->category_group_id)
            ->where('id', '!=', $product->id)
            ->with(['category.categoryGroup', 'categoryGroup', 'media'])
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->formatProductCard($p));

        return Inertia::render('Products/Show', [
            'product' => $this->formatProductDetail($product),
            'relatedProducts' => $relatedProducts,
            'initialVariantSku' => $variantSku,
        ]);
    }

    private function formatProductDetail(Product $product): array
    {
        $priceSold = $product->price_sold;
        $priceSales = $product->price_sales;
        $discountData = null;

        if ($priceSales && $priceSales > 0 && $priceSales < $priceSold) {
            $percentage = round((($priceSold - $priceSales) / $priceSold) * 100);
            $discountData = [
                'percentage' => $percentage,
                'new_price' => $priceSales,
            ];
        } else {
            $discount = $this->couponService->getAutomaticDiscount($product);
            if ($discount) {
                $discountAmount = $this->couponService->calculateDiscount($discount, $priceSold);
                $discountData = [
                    'percentage' => $discount->discount_type->value === 'percentage' ? $discount->discount_value : null,
                    'new_price' => $priceSold - $discountAmount,
                ];
            }
        }

        $images = $product->getMedia('default')->map(fn ($media) => [
            'id' => $media->id,
            'url' => MediaUrl::resolve($media, 'webp'),
            'thumb' => MediaUrl::resolve($media, 'thumb', 'webp'),
        ])->values();

        // Si el producto no tiene imágenes propias, usar las imágenes de las variantes
        // (la primera imagen de cada variante), tomando la primera como principal.
        if ($images->isEmpty()) {
            $images = $product->variants
                ->map(fn ($variant) => $variant->getFirstMedia('variant'))
                ->filter()
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => MediaUrl::resolve($media, 'webp'),
                    'thumb' => MediaUrl::resolve($media, 'thumb', 'webp'),
                ])
                ->values();
        }

        $variants = $product->variants->map(function ($variant) {
            $media = $variant->getFirstMedia('variant');

            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'color' => $variant->color ? [
                    'id' => $variant->color->id,
                    'name' => $variant->color->name,
                    'hex' => $variant->color->rgb_color,
                ] : null,
                'size' => $variant->size ? [
                    'id' => $variant->size->id,
                    'name' => $variant->size->name,
                ] : null,
                'price_sold' => $variant->price_sold,
                'price_sales' => $variant->price_sales,
                'stock' => $variant->stock,
                'image' => $media ? [
                    'id' => $media->id,
                    'url' => MediaUrl::resolve($media, 'webp'),
                    'thumb' => MediaUrl::resolve($media, 'thumb', 'webp'),
                ] : null,
            ];
        });

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'description' => $product->description,
            'is_customizable' => $product->is_customizable,
            'customization_label' => $product->customization_label,
            'customization_price' => $product->customization_price,
            'price' => $priceSold,
            'price_without_tax' => $product->price_without_tax,
            'discount' => $discountData,
            'images' => $images,
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'category_group' => $this->formatGroup($product),
            'variants' => $variants,
            'dimensions' => [
                'weight' => $product->dimension_weight,
                'height' => $product->dimension_height,
                'width' => $product->dimension_width,
                'length' => $product->dimension_length,
            ],
            'tags' => $product->tags->pluck('name'),
        ];
    }

    private function formatProductCard(Product $product): array
    {
        $priceSold = $product->price_sold;
        $priceSales = $product->price_sales;
        $discountData = null;

        if ($priceSales && $priceSales > 0 && $priceSales < $priceSold) {
            $percentage = round((($priceSold - $priceSales) / $priceSold) * 100);
            $discountData = [
                'percentage' => $percentage,
                'new_price' => $priceSales,
            ];
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $priceSold,
            'discount' => $discountData,
            'image' => MediaUrl::firstFor($product, 'default', 'thumb', 'webp'),
            'category' => $product->category?->name ?? $product->categoryGroup?->name,
            'group_slug' => $product->category?->categoryGroup?->slug ?? $product->categoryGroup?->slug,
        ];
    }

    /**
     * Grupo (categoría padre) del producto, tomado de la relación directa o
     * derivado de la subcategoría. Null si el producto no tiene categoría.
     *
     * @return array{name: string, slug: string}|null
     */
    private function formatGroup(Product $product): ?array
    {
        $group = $product->categoryGroup ?? $product->category?->categoryGroup;

        return $group ? [
            'name' => $group->name,
            'slug' => $group->slug,
        ] : null;
    }
}
