<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'is_encrypted'];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if ($this->is_encrypted) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $value;
    }

    public function setValueAttribute($value): void
    {
        if ($value !== null && $this->is_encrypted) {
            $this->attributes['value'] = Crypt::encryptString($value);

            return;
        }

        $this->attributes['value'] = $value;
    }
}
