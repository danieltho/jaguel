<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'document',
        'email',
        'phone',
        'whatsapp',
        'address',
        'address_number',
        'department',
        'zone',
        'city',
        'state',
        'country_iso',
        'receive_offers',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'receive_offers' => 'boolean',
        ];
    }
}
