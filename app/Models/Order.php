<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
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
        'coupon_id',
        'status',
    ];

    protected $attributes = [
        'subtotal' => 0,
        'total' => 0,
        'shipping_cost' => 0,
    ];

    protected $casts = [
        'order_number' => 'integer',
        'total' => 'integer',
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'status' => OrderStatusEnum::class,
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $this->total = $this->subtotal + $this->shipping_cost;
        $this->saveQuietly();
    }
}
