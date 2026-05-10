<?php

namespace App\Core;

final class App
{
    private static array $config = [];

    public static function bootstrap(): void
    {
        Env::load(base_path('.env'));
        self::loadConfig();
        date_default_timezone_set((string) self::config('app.timezone', 'UTC'));
    }

    public static function loadConfig(): void
    {
        self::$config = [
            'app' => require app_path('config/app.php'),
            'database' => require app_path('config/database.php'),
            'config' => require app_path('config/config.php'),
        ];
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
