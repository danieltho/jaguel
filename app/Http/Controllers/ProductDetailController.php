<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CouponService;
use Inertia\Inertia;
use Inertia\Response;

class ProductDetailController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category.categoryGroup',
                'variants.color',
                'variants.size',
                'variants.media',
                'media',
                'tags',
            ])
            ->firstOrFail();

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category.categoryGroup'])
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->formatProductCard($p));

        return Inertia::render('Products/Show', [
            'product' => $this->formatProductDetail($product),
            'relatedProducts' => $relatedProducts,
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
            'url' => $media->getUrl('webp'),
            'thumb' => $media->getUrl('thumb'),
        ])->values();

        $variants = $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'color' => $variant->color ? [
                'id' => $variant->color->id,
                'name' => $variant->color->name,
                'hex' => $variant->color->hex ?? null,
            ] : null,
            'size' => $variant->size ? [
                'id' => $variant->size->id,
                'name' => $variant->size->name,
            ] : null,
            'price_sold' => $variant->price_sold,
            'price_sales' => $variant->price_sales,
            'stock' => $variant->stock,
            'image' => $variant->getFirstMediaUrl('variant', 'thumb') ?: null,
        ]);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $priceSold,
            'discount' => $discountData,
            'images' => $images,
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
                'group' => $product->category->categoryGroup ? [
                    'name' => $product->category->categoryGroup->name,
                    'slug' => $product->category->categoryGroup->slug,
                ] : null,
            ] : null,
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
            'image' => $product->getFirstMediaUrl('default', 'thumb'),
            'category' => $product->category?->name,
            'group_slug' => $product->category?->categoryGroup?->slug,
        ];
    }
}
