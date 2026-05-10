<?php

namespace App\services;

final class RandomizerService
{
    private array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    ];

    private array $facebookUserAgents = [
        'Mozilla/5.0 (Linux; Android 10; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; U; Android 8.1.0; en-US; Mobile) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
    ];

    public function delaySeconds(?int $min = null, ?int $max = null): int
    {
        $min = $min ?? (int) config('config.bot.min_delay_seconds', 20);
        $max = $max ?? (int) config('config.bot.max_delay_seconds', 90);

        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        return random_int(max(1, $min), max(1, $max));
    }

    public function userAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)] ?: (string) config('config.bot.default_user_agent');
    }

    public function facebookUserAgent(): string
    {
        return $this->facebookUserAgents[array_rand($this->facebookUserAgents)];
    }

    public function pick(array $items): ?array
    {
        if ($items === []) {
            return null;
        }

        return $items[array_rand($items)];
    }
}
