<?php

namespace App\Services;

use App\Enums\CouponScopeEnum;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\MediaUrl;

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

            $customized = ! empty($item['customized']) && $product->is_customizable;
            $customizationPrice = $customized ? (float) $product->customization_price : 0;

            $unitPrice = $this->getItemPrice($product, $variant) + $customizationPrice;

            return [
                'cart_key' => $key,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => MediaUrl::firstFor($variant, 'variant', 'thumb', 'webp')
                    ?: MediaUrl::firstFor($product, 'default', 'thumb', 'webp'),
                'color' => $variant?->color?->name,
                'size' => $variant?->size?->name,
                'customized' => $customized,
                'customization_label' => $customized ? ($product->customization_label ?: 'Grabado Personalizado') : null,
                'customization_price' => $customizationPrice,
                'unit_price' => $unitPrice,
                'quantity' => $item['quantity'],
                'total' => $unitPrice * $item['quantity'],
            ];
        })->filter()->values()->all();
    }

    public function addItem(int $productId, ?int $variantId, int $quantity = 1, bool $customized = false): void
    {
        $cart = session('cart', []);
        $key = $this->makeKey($productId, $variantId, $customized);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'customized' => $customized,
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

            $customizationPrice = (! empty($item['customized']) && $product && $product->is_customizable)
                ? (float) $product->customization_price
                : 0;

            return ($this->getItemPrice($product, $variant) + $customizationPrice) * $item['quantity'];
        });
    }

    public function getItemCount(): int
    {
        return collect(session('cart', []))->sum('quantity');
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Cupón no encontrado o inactivo'];
        }

        $couponService = app(CouponService::class);
        $subtotal = $this->getSubtotal();

        $validation = $couponService->validateCoupon($coupon, null, null, $subtotal);
        if (! ($validation['valid'] ?? false)) {
            return ['success' => false, 'message' => $validation['error'] ?? 'Cupón no válido'];
        }

        if (! $this->cartHasEligibleProduct($coupon, $couponService)) {
            return ['success' => false, 'message' => 'Este cupón no aplica a los productos de tu carrito.'];
        }

        $discount = $couponService->calculateDiscount($coupon, $subtotal);

        if ($discount <= 0) {
            return ['success' => false, 'message' => 'El cupón no genera descuento sobre este carrito.'];
        }

        session(['cart_coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $discount,
        ]]);

        return ['success' => true, 'message' => 'Cupón aplicado', 'discount' => $discount];
    }

    /**
     * Verifica que al menos un producto del carrito sea elegible para el cupón
     * según su alcance (general / categoría / producto).
     */
    private function cartHasEligibleProduct(Coupon $coupon, CouponService $couponService): bool
    {
        if ($coupon->scope === CouponScopeEnum::GENERAL) {
            return true;
        }

        $productIds = collect(session('cart', []))->pluck('product_id')->unique();

        if ($productIds->isEmpty()) {
            return false;
        }

        return Product::whereIn('id', $productIds)
            ->get()
            ->contains(fn (Product $product) => $couponService->isProductEligible($coupon, $product));
    }

    public function removeCoupon(): void
    {
        session()->forget('cart_coupon');
    }

    /**
     * Re-valida el cupón guardado en sesión contra el estado actual del carrito.
     * Si ya no es válido (p. ej. el subtotal cayó por debajo del monto mínimo
     * al borrar productos) lo remueve; si sigue válido recalcula su descuento.
     *
     * @return array{coupon: ?array, removed: bool, message?: string}
     */
    public function revalidateCoupon(): array
    {
        $stored = session('cart_coupon');

        if (! $stored) {
            return ['coupon' => null, 'removed' => false];
        }

        $coupon = Coupon::where('id', $stored['id'])
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            $this->removeCoupon();

            return ['coupon' => null, 'removed' => true, 'message' => 'El cupón ya no está disponible'];
        }

        $couponService = app(CouponService::class);
        $subtotal = $this->getSubtotal();

        $validation = $couponService->validateCoupon($coupon, null, null, $subtotal);
        if (! ($validation['valid'] ?? false)) {
            $this->removeCoupon();

            return ['coupon' => null, 'removed' => true, 'message' => $validation['error'] ?? 'El cupón ya no es válido'];
        }

        if (! $this->cartHasEligibleProduct($coupon, $couponService)) {
            $this->removeCoupon();

            return ['coupon' => null, 'removed' => true, 'message' => 'Este cupón ya no aplica a los productos de tu carrito.'];
        }

        $discount = $couponService->calculateDiscount($coupon, $subtotal);

        if ($discount <= 0) {
            $this->removeCoupon();

            return ['coupon' => null, 'removed' => true, 'message' => 'El cupón ya no genera descuento sobre este carrito.'];
        }

        $updated = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $discount,
        ];

        session(['cart_coupon' => $updated]);

        return ['coupon' => $updated, 'removed' => false];
    }

    public function getCoupon(): ?array
    {
        return $this->revalidateCoupon()['coupon'];
    }

    private function makeKey(int $productId, ?int $variantId, bool $customized = false): string
    {
        return $productId.'-'.($variantId ?? 'null').'-c'.($customized ? '1' : '0');
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
