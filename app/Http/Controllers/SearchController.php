<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index(Request $request): Response
    {
        $query = $request->query('q', '');
        $products = collect();

        if (strlen($query) >= 2) {
            $products = Product::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->with(['category.categoryGroup'])
                ->paginate(12)
                ->through(fn ($product) => $this->formatProduct($product));
        }

        return Inertia::render('Search/Index', [
            'products' => $products,
            'query' => $query,
        ]);
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
