<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Observers\OrderObserver;
use App\Services\CouponService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([OrderObserver::class])]
class Order extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        'mp_preference_id',
        'mp_payment_id',
        'status',
        'document_number',
        'document_type',
        'wants_factura_a',
        'recipient_firstname',
        'recipient_lastname',
        'recipient_phone',
        'recipient_address',
        'recipient_department',
        'recipient_city',
        'recipient_state',
        'delivery_type',
        'shipping_method',
        'shipping_method_id',
    ];

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function registerMediaCollections(): void
    {
        // El tipo de archivo se valida en el controlador (mimes:pdf,jpg,jpeg,png),
        // que es el punto donde se reporta el error al usuario.
        $this->addMediaCollection('payment_receipt')->singleFile();
    }

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
        'payment_reminder_1_sent_at' => 'datetime',
        'payment_reminder_2_sent_at' => 'datetime',
        'wants_factura_a' => 'boolean',
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

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));

        // Calcular descuento si hay cupón
        $this->discount_amount = 0;
        if ($this->coupon_id && $this->coupon) {
            $couponService = app(CouponService::class);
            $this->discount_amount = $couponService->calculateDiscount($this->coupon, $this->subtotal);
        }

        // Total = Subtotal - Descuento + Envío
        $this->total = $this->subtotal - $this->discount_amount + $this->shipping_cost;
        $this->saveQuietly();
    }
}
