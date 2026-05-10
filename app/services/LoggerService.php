<?php

namespace App\services;

use App\helpers\LoggerHelper;
use App\models\Log;

final class LoggerService
{
    private Log $logs;

    public function __construct(?Log $logs = null)
    {
        $this->logs = $logs ?? new Log();
    }

    public function debug(string $status, string $message, ?int $postId = null, array $context = []): void
    {
        $this->write('debug', $status, $message, $postId, $context);
    }

    public function info(string $status, string $message, ?int $postId = null, array $context = []): void
    {
        $this->write('info', $status, $message, $postId, $context);
    }

    public function warning(string $status, string $message, ?int $postId = null, array $context = []): void
    {
        $this->write('warning', $status, $message, $postId, $context);
    }

    public function error(string $status, string $message, ?int $postId = null, array $context = []): void
    {
        $this->write('error', $status, $message, $postId, $context);
    }

    private function write(string $level, string $status, string $message, ?int $postId, array $context): void
    {
        LoggerHelper::file($level, $message, ['status' => $status, 'post_id' => $postId] + $context);

        try {
            $this->logs->create($level, $status, $message, $postId, $context);
        } catch (\Throwable $exception) {
            LoggerHelper::file('error', 'Database log write failed', ['error' => $exception->getMessage()]);
        }
    }
}
