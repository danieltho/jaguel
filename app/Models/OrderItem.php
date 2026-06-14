<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'is_customized',
        'customization_price',
        'customization_label',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'is_customized' => 'boolean',
        'customization_price' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (OrderItem $item) {
            $item->order->recalculateTotals();
        });

        static::deleted(function (OrderItem $item) {
            $item->order->recalculateTotals();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
