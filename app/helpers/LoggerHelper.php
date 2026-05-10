<?php

namespace App\helpers;

final class LoggerHelper
{
    public static function file(string $level, string $message, array $context = []): void
    {
        $file = (string) config('config.logging.file', storage_path('logs/app.log'));
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = json_encode([
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
