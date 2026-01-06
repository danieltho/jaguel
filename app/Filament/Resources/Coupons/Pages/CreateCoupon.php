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
        if ($data['discount_type'] === DiscountTypeEnum::FIXED_AMOUNT->value) {
            $data['discount_value'] = $data['discount_value'] * 100;
        }

        if (isset($data['minimum_purchase']) && $data['minimum_purchase']) {
            $data['minimum_purchase'] = $data['minimum_purchase'] * 100;
        }

        return $data;
    }
}
