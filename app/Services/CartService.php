<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class CartService
{
    public function getItems(): array
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return [];
        }

        $productIds = collect($cart)->pluck('product_id')->unique();
        $variantIds = collect($cart)->pluck('variant_id')->filter()->unique();

        $products = Product::whereIn('id', $productIds)
            ->with(['category', 'media'])
            ->get()
            ->keyBy('id');

        $variants = $variantIds->isNotEmpty()
            ? ProductVariant::whereIn('id', $variantIds)
                ->with(['color', 'size', 'media'])
                ->get()
                ->keyBy('id')
            : collect();

        return collect($cart)->map(function ($item, $key) use ($products, $variants) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                return null;
            }

            $variant = isset($item['variant_id']) ? $variants->get($item['variant_id']) : null;

            $unitPrice = $this->getItemPrice($product, $variant);

            return [
                'cart_key' => $key,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $variant?->getFirstMediaUrl('variant', 'thumb')
                    ?: $product->getFirstMediaUrl('default', 'thumb'),
                'color' => $variant?->color?->name,
                'size' => $variant?->size?->name,
                'unit_price' => $unitPrice,
                'quantity' => $item['quantity'],
                'total' => $unitPrice * $item['quantity'],
            ];
        })->filter()->values()->all();
    }

    public function addItem(int $productId, ?int $variantId, int $quantity = 1): void
    {
        $cart = session('cart', []);
        $key = $this->makeKey($productId, $variantId);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        session(['cart' => $cart]);
    }

    public function updateQuantity(string $cartKey, int $quantity): void
    {
        $cart = session('cart', []);

        if (isset($cart[$cartKey])) {
            if ($quantity <= 0) {
                unset($cart[$cartKey]);
            } else {
                $cart[$cartKey]['quantity'] = $quantity;
            }
        }

        session(['cart' => $cart]);
    }

    public function removeItem(string $cartKey): void
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);
    }

    public function clear(): void
    {
        session()->forget('cart');
        session()->forget('cart_coupon');
    }

    public function getSubtotal(): float
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return 0;
        }

        $productIds = collect($cart)->pluck('product_id')->unique();
        $variantIds = collect($cart)->pluck('variant_id')->filter()->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $variants = $variantIds->isNotEmpty()
            ? ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id')
            : collect();

        return collect($cart)->sum(function ($item) use ($products, $variants) {
            $product = $products->get($item['product_id']);
            $variant = isset($item['variant_id']) ? $variants->get($item['variant_id']) : null;

            return $this->getItemPrice($product, $variant) * $item['quantity'];
        });
    }

    public function getItemCount(): int
    {
        return collect(session('cart', []))->sum('quantity');
    }

    public function applyCoupon(string $code): array
    {
        $coupon = \App\Models\Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Cupón no encontrado'];
        }

        $couponService = app(CouponService::class);
        $discount = $couponService->calculateDiscount($coupon, $this->getSubtotal());

        session(['cart_coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $discount,
        ]]);

        return ['success' => true, 'message' => 'Cupón aplicado', 'discount' => $discount];
    }

    public function removeCoupon(): void
    {
        session()->forget('cart_coupon');
    }

    public function getCoupon(): ?array
    {
        return session('cart_coupon');
    }

    private function makeKey(int $productId, ?int $variantId): string
    {
        return $productId.'-'.($variantId ?? 'null');
    }

    private function getItemPrice(?Product $product, ?ProductVariant $variant): float
    {
        if (! $product) {
            return 0;
        }

        if ($variant) {
            $price = $variant->price_sales > 0 ? $variant->price_sales : $variant->price_sold;

            return $price > 0 ? $price : ($product->price_sales > 0 ? $product->price_sales : $product->price_sold);
        }

        return $product->price_sales > 0 ? $product->price_sales : $product->price_sold;
    }
}
