<?php

namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Models\Product;
use App\Services\CouponService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function __invoke(): Response
    {
        $categoryGroups = CategoryGroup::with('categories')->get()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'image' => $group->getFirstMediaUrl('default', 'thumb-xl'),
                'categories' => $group->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ]),
            ];
        });

        $featuredProducts = Product::where('is_active', true)
            ->with(['category.categoryGroup'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product));

        return Inertia::render('Home', [
            'categoryGroups' => $categoryGroups,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    private function formatProduct(Product $product): array
    {
        $priceSold = $product->price_sold;
        $priceSales = $product->price_sales;
        $discountData = null;

        // If there's a manual sale price, use it
        if ($priceSales && $priceSales > 0 && $priceSales < $priceSold) {
            $percentage = round((($priceSold - $priceSales) / $priceSold) * 100);
            $discountData = [
                'percentage' => $percentage,
                'new_price' => $priceSales,
            ];
        } else {
            // Check for automatic coupon discount
            $discount = $this->couponService->getAutomaticDiscount($product);
            if ($discount) {
                $discountAmount = $this->couponService->calculateDiscount($discount, $priceSold);
                $discountData = [
                    'percentage' => $discount->discount_type->value === 'percentage' ? $discount->discount_value : null,
                    'new_price' => $priceSold - $discountAmount,
                ];
            }
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
