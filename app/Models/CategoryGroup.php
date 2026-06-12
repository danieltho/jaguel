<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CategoryGroup extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    protected $fillable = ['name', 'slug'];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb-xl')
            ->fit(Fit::Max, 848, 560)
            ->format('webp')
            ->quality(80)
            ->sharpen(10);

        $this->addMediaConversion('thumb-md')
            ->fit(Fit::Max, 240, 200)
            ->format('webp')
            ->quality(80)
            ->sharpen(10);

        $this->addMediaConversion('thumb-xs')
            ->fit(Fit::Max, 358, 200)
            ->format('webp')
            ->quality(80)
            ->sharpen(10);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
