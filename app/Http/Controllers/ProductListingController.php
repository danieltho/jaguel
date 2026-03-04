<?php

namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductListingController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index(Request $request): Response
    {
        $sort = $request->query('sort', 'newest');

        $query = Product::where('is_active', true)
            ->with(['category.categoryGroup']);

        $query = $this->applySorting($query, $sort);

        $products = $query->paginate(12)->through(
            fn ($product) => $this->formatProduct($product)
        );

        $categoryGroups = CategoryGroup::with('categories')->get()->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'slug' => $group->slug,
            'categories' => $group->categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]),
        ]);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'sort' => $sort,
            'activeGroup' => null,
            'activeCategory' => null,
            'categoryGroups' => $categoryGroups,
        ]);
    }

    public function byGroup(Request $request, string $groupSlug): Response
    {
        $group = CategoryGroup::with('categories')
            ->where('slug', $groupSlug)
            ->firstOrFail();

        $sort = $request->query('sort', 'newest');
        $categorySlug = $request->query('category');

        $query = Product::where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('category_group_id', $group->id))
            ->with(['category.categoryGroup']);

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $query = $this->applySorting($query, $sort);

        $products = $query->paginate(12)->through(
            fn ($product) => $this->formatProduct($product)
        );

        return Inertia::render('Products/Index', [
            'products' => $products,
            'sort' => $sort,
            'activeGroup' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'categories' => $group->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ]),
            ],
            'activeCategory' => $categorySlug,
            'categoryGroups' => null,
        ]);
    }

    private function applySorting($query, string $sort)
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price_sold', 'asc'),
            'price_desc' => $query->orderBy('price_sold', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };
    }

    private function formatProduct(Product $product): array
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
