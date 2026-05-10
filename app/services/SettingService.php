<?php

namespace App\services;

use App\models\Setting;

final class SettingService
{
    private array $cache = [];

    public function __construct(private ?Setting $settings = null)
    {
        $this->settings ??= new Setting();
    }

    public function string(string $key, mixed $default = ''): string
    {
        $value = $this->get($key, $default);
        return trim((string) $value);
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default ? '1' : '0');
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function get(string $key, mixed $default): mixed
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        try {
            $this->cache[$key] = $this->settings->get($key, $default);
        } catch (\Throwable) {
            $this->cache[$key] = $default;
        }

        return $this->cache[$key];
    }
}
