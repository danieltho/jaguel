<?php

namespace App\Filament\Widgets;

use App\Enums\CouponScopeEnum;
use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use App\Models\Coupon;
use Filament\Widgets\Widget;

class ActiveDiscountsWidget extends Widget
{
    protected string $view = 'filament.widgets.active-discounts-widget';

    protected int | string | array $columnSpan = 'full';

    public function getDiscounts()
    {
        return Coupon::where('type', CouponTypeEnum::AUTOMATIC_DISCOUNT)
            ->where('scope', CouponScopeEnum::GENERAL)
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
            ->get();
    }

    public function formatDiscount(Coupon $coupon): string
    {
        if ($coupon->discount_type === DiscountTypeEnum::PERCENTAGE) {
            return $coupon->discount_value . '%';
        }

        return '$' . number_format($coupon->discount_value / 100, 2);
    }

    public function formatExpiration(Coupon $coupon): string
    {
        if (!$coupon->expires_at) {
            return 'Sin fecha de expiración';
        }

        return 'Válido hasta: ' . $coupon->expires_at->format('d/m/Y H:i');
    }
}
