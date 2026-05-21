<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'days_label',
        'delivery_type',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
