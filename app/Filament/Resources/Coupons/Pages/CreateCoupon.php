<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Enums\DiscountTypeEnum;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar código único si no se proporcionó uno
        if ($data['type'] === \App\Enums\CouponTypeEnum::COUPON->value && empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode();
        }

        if ($data['discount_type'] === DiscountTypeEnum::FIXED_AMOUNT->value) {
            $data['discount_value'] = $data['discount_value'] * 100;
        }

        if (isset($data['minimum_purchase']) && $data['minimum_purchase']) {
            $data['minimum_purchase'] = $data['minimum_purchase'] * 100;
        }

        return $data;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (\App\Models\Coupon::where('code', $code)->exists());

        return $code;
    }
}
