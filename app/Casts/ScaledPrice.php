<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ScaledPrice implements CastsAttributes
{
    private const SCALE = 1000;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        return $value === null ? null : (float) $value / self::SCALE;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null || $value === '' ? null : (int) round((float) $value * self::SCALE);
    }
}
