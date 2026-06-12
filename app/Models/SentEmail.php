<?php

namespace App\Models;

use App\Enums\SentEmailStatusEnum;
use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    protected $fillable = [
        'to_address',
        'subject',
        'mailable',
        'mailer',
        'message_id',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'status' => SentEmailStatusEnum::class,
        'sent_at' => 'datetime',
    ];
}
