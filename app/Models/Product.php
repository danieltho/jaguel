<?php

namespace App\Models;

use App\Casts\ScaledPrice;
use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'link_video',
        'price_sold',
        'price_without_tax',
        'price_sales',
        'price_provider',
        'price_cost',
        'stock',
        'category_id',
        'category_group_id',
        'type',
        'is_active',
        'is_customizable',
        'customization_label',
        'customization_price',
        'dimension_weight',
        'dimension_height',
        'dimension_width',
        'dimension_length',
    ];

    protected $casts = [
        'type' => ProductTypeEnum::class,
        'is_active' => 'boolean',
        'is_customizable' => 'boolean',
        'customization_price' => ScaledPrice::class,
        'price_sold' => ScaledPrice::class,
        'price_without_tax' => ScaledPrice::class,
        'price_sales' => ScaledPrice::class,
        'price_provider' => ScaledPrice::class,
        'price_cost' => ScaledPrice::class,
        'dimension_weight' => 'float',
        'dimension_height' => 'float',
        'dimension_width' => 'float',
        'dimension_length' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(8);
            }
        });

        static::created(function (Product $product) {
            $finalSlug = Str::slug($product->name).'-'.$product->id;
            if ($product->slug !== $finalSlug) {
                $product->slug = $finalSlug;
                $product->saveQuietly();
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name')) {
                $product->slug = Str::slug($product->name).'-'.$product->id;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryGroup(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82);

        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 600, 600)
            ->format('webp')
            ->quality(78)
            ->sharpen(10);
    }
}
