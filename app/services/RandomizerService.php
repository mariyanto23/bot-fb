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
        'NokiaC3-00/5.0 (08.63) Profile/MIDP-2.1 Configuration/CLDC-1.1',
        'Mozilla/5.0 (Series40; Nokia501/10.0.2) Profile/MIDP-2.1 Configuration/CLDC-1.1',
        'Opera/9.80 (J2ME/MIDP; Opera Mini/4.5.33867/191.249; U; en) Presto/2.12.423 Version/12.16',
        'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)',
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
