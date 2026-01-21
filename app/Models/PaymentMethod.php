<?php

namespace App\Models;

use App\Enums\PaymentMethodTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'description',
        'max_installments',
        'mercadopago_public_key',
        'mercadopago_access_token',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'type' => PaymentMethodTypeEnum::class,
        'is_active' => 'boolean',
        'max_installments' => 'integer',
        'sort_order' => 'integer',
    ];
}
