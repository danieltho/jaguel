<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'email',
        'postal_code',
        'customer_id',
        'total',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'coupon_id',
        'payment_method_id',
        'payment_status',
        'status',
    ];

    protected $attributes = [
        'subtotal' => 0,
        'total' => 0,
        'shipping_cost' => 0,
        'discount_amount' => 0,
    ];

    protected $casts = [
        'order_number' => 'integer',
        'total' => 'integer',
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'discount_amount' => 'integer',
        'status' => OrderStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number = (static::max('order_number') ?? 0) + 1;
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        
        // Calcular descuento si hay cupón
        $this->discount_amount = 0;
        if ($this->coupon_id && $this->coupon) {
            $couponService = app(\App\Services\CouponService::class);
            $this->discount_amount = $couponService->calculateDiscount($this->coupon, $this->subtotal);
        }
        
        // Total = Subtotal - Descuento + Envío
        $this->total = $this->subtotal - $this->discount_amount + $this->shipping_cost;
        $this->saveQuietly();
    }
}
