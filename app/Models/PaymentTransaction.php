<?php

namespace App\Models;

use App\Enums\MpPaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'mp_payment_id',
        'mp_preference_id',
        'mp_status',
        'mp_status_detail',
        'mp_payment_type',
        'mp_payment_method',
        'installments',
        'transaction_amount',
        'currency',
        'payer_email',
        'raw_response',
        'processed_at',
    ];

    protected $casts = [
        'mp_status' => MpPaymentStatusEnum::class,
        'installments' => 'integer',
        'transaction_amount' => 'integer',
        'raw_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
