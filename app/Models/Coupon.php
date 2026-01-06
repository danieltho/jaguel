<?php

namespace App\Models;

use App\Enums\CouponScopeEnum;
use App\Enums\CouponTypeEnum;
use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'scope',
        'minimum_purchase',
        'max_uses',
        'max_uses_per_user',
        'current_uses',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'type' => CouponTypeEnum::class,
        'discount_type' => DiscountTypeEnum::class,
        'scope' => CouponScopeEnum::class,
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
