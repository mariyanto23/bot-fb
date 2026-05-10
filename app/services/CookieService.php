<?php

namespace App\services;

final class CookieService
{
    public function path(): string
    {
        $path = (string) config('config.facebook.cookie_file');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $path;
    }

    public function saveRaw(string $cookieContent): void
    {
        file_put_contents($this->path(), $cookieContent, LOCK_EX);
    }

    public function exists(): bool
    {
        $path = $this->path();
        return is_file($path) && filesize($path) > 0;
    }

    public function clear(): void
    {
        $path = $this->path();
        if (is_file($path)) {
            unlink($path);
        }
    }
}
