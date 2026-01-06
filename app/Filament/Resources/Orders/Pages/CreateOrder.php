<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convertir precios a centavos
        $data['price'] = ($data['price'] ?? 0) * 100;
        $data['subtotal'] = ($data['subtotal'] ?? $data['price']) * 100;
        $data['discount_amount'] = ($data['discount_amount'] ?? 0) * 100;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Si hay un cupón aplicado, registrar el uso
        if ($this->record->coupon_id) {
            $coupon = Coupon::find($this->record->coupon_id);
            $user = User::find($this->record->user_id);

            if ($coupon && $user) {
                $couponService = new CouponService();
                $couponService->applyCoupon($coupon, $this->record, $user);
            }
        }
    }
}
