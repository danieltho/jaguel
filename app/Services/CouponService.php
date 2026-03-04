<?php

namespace App\Services;

use App\Enums\CouponScopeEnum;
use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Valida si un cupón con código puede ser aplicado
     */
    public function validate(
        string $code,
        Customer $customer,
        Product $product,
        int $subtotal
    ): array {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'error' => 'Cupón no encontrado'];
        }

        return $this->validateCoupon($coupon, $user, $product, $subtotal);
    }

    /**
     * Valida un cupón existente
     */
    public function validateCoupon(
        Coupon $coupon,
        Customer $customer,
        Product $product,
        int $subtotal
    ): array {
        // Verificar fechas
        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return ['valid' => false, 'error' => 'El cupón aún no está activo'];
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return ['valid' => false, 'error' => 'El cupón ha expirado'];
        }

        // Verificar limite total
        if ($coupon->max_uses && $coupon->current_uses >= $coupon->max_uses) {
            return ['valid' => false, 'error' => 'El cupón ha alcanzado su límite de usos'];
        }

        // Verificar limite por usuario
        if ($coupon->max_uses_per_user) {
            $userUsages = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $customer->id)
                ->count();

            if ($userUsages >= $coupon->max_uses_per_user) {
                return ['valid' => false, 'error' => 'Has alcanzado el límite de usos para este cupón'];
            }
        }

        // Verificar monto mínimo
        if ($coupon->minimum_purchase && $subtotal < $coupon->minimum_purchase) {
            $minFormatted = number_format($coupon->minimum_purchase / 100, 2);
            return ['valid' => false, 'error' => "El monto mínimo de compra es \${$minFormatted}"];
        }

        // Verificar alcance
        if (!$this->isProductEligible($coupon, $product)) {
            return ['valid' => false, 'error' => 'Este cupón no aplica al producto seleccionado'];
        }

        return ['valid' => true, 'coupon' => $coupon];
    }

    /**
     * Verifica si el producto es elegible para el cupón
     */
    public function isProductEligible(Coupon $coupon, Product $product): bool
    {
        return match ($coupon->scope) {
            CouponScopeEnum::GENERAL => true,
            CouponScopeEnum::CATEGORY => $coupon->categories()
                ->where('categories.id', $product->category_id)
                ->exists(),
            CouponScopeEnum::PRODUCT => $coupon->products()
                ->where('products.id', $product->id)
                ->exists(),
        };
    }

    /**
     * Calcula el descuento a aplicar
     */
    public function calculateDiscount(Coupon $coupon, int $subtotal): int
    {
        return match ($coupon->discount_type) {
            DiscountTypeEnum::PERCENTAGE => (int) floor($subtotal * $coupon->discount_value / 100),
            DiscountTypeEnum::FIXED_AMOUNT => min($coupon->discount_value, $subtotal),
        };
    }

    /**
     * Aplica el cupón a una orden
     */
    public function applyCoupon(Coupon $coupon, Order $order, Customer $customer): void
    {
        DB::transaction(function () use ($coupon, $order, $customer) {
            // Incrementar contador de usos
            $coupon->increment('current_uses');

            // Registrar uso
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'discount_applied' => $order->discount_amount,
            ]);
        });
    }

    /**
     * Obtiene descuentos automáticos aplicables a un producto
     */
    public function getAutomaticDiscount(Product $product): ?Coupon
    {
        return Coupon::where('type', CouponTypeEnum::AUTOMATIC_DISCOUNT)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereColumn('current_uses', '<', 'max_uses');
            })
            ->get()
            ->first(fn (Coupon $coupon) => $this->isProductEligible($coupon, $product));
    }
}
