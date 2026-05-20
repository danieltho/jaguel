<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Color extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'rgb_color',
    ];

    protected function rgbColor(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if ($value === null || $value === '') {
                    return $value;
                }

                $trimmed = ltrim(trim($value), '#');

                return '#' . strtoupper($trimmed);
            },
        );
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
