<?php

namespace App\Core;

final class CronLock
{
    private string $path;
    private $handle = null;

    public function __construct(string $name)
    {
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', $name) ?: 'cron';
        $this->path = storage_path('cache/' . $safeName . '.lock');
    }

    public function acquire(): bool
    {
        $this->handle = fopen($this->path, 'c+');
        if ($this->handle === false) {
            return false;
        }

        if (!flock($this->handle, LOCK_EX | LOCK_NB)) {
            return false;
        }

        ftruncate($this->handle, 0);
        fwrite($this->handle, (string) getmypid());
        return true;
    }

    public function release(): void
    {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
    }
}
