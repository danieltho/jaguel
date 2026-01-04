<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CategoryGroup extends Model implements HasMedia
{
    use InteractsWithMedia;
    public $timestamps = false;

    protected $fillable = ['name'];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb-xl')
            ->width(424)
            ->height(280)
            ->sharpen(10);

        $this->addMediaConversion('thumb-md')
            ->width(240)
            ->height(200)
            ->sharpen(10);


        $this->addMediaConversion('thumb-xs')
            ->width(358)
            ->height(200)
            ->sharpen(10);
    }

    public function categories(): hasMany
    {
        return $this->hasMany(Category::class);
    }
}
