<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Coupon;
use App\Models\Customer;
use App\Services\CouponService;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        // Recalcular totales según los items y el cupón.
        $this->record->refresh();
        $this->record->recalculateTotals();

        // Si hay un cupón aplicado, registrar el uso
        if ($this->record->coupon_id && $this->record->customer_id) {
            $coupon = Coupon::find($this->record->coupon_id);
            $customer = Customer::find($this->record->customer_id);

            if ($coupon && $customer) {
                app(CouponService::class)->applyCoupon($coupon, $this->record, $customer);
            }
        }
    }
}
