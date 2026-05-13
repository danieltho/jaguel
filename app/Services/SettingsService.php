<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    private const CACHE_TTL = 3600;

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return $all[$group][$key] ?? $default;
    }

    public function set(string $group, string $key, ?string $value, bool $encrypted = false): void
    {
        $setting = Setting::firstOrNew(['group' => $group, 'key' => $key]);
        $setting->is_encrypted = $encrypted;
        $setting->value = $value;
        $setting->save();

        $this->flush();
    }

    public function setMany(string $group, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            $this->set($group, $key, $value, in_array($key, $encryptedKeys, true));
        }
    }

    public function group(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $result = [];
            foreach (Setting::all() as $setting) {
                $result[$setting->group][$setting->key] = $setting->value;
            }

            return $result;
        });
    }
}
