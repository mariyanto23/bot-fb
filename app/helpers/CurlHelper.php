<?php

namespace App\helpers;

final class CurlHelper
{
    public static function request(string $url, array $options = []): array
    {
        $ch = curl_init($url);
        $headers = $options['headers'] ?? [];
        $timeout = (int) ($options['timeout'] ?? 30);
        $userAgent = (string) ($options['user_agent'] ?? config('config.bot.default_user_agent', 'Mozilla/5.0'));

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADER => true,
        ]);

        if (!empty($options['cookie_file'])) {
            $cookieFile = (string) $options['cookie_file'];
            $cookieContent = is_file($cookieFile) ? trim((string) file_get_contents($cookieFile)) : '';

            if ($cookieContent !== '' && !str_contains($cookieContent, "\t")) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookieContent);
            } else {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            }

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        }

        if (!empty($options['post'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['post']);
        }

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $headerSize = (int) ($info['header_size'] ?? 0);
        $body = is_string($raw) ? substr($raw, $headerSize) : '';

        return [
            'ok' => $error === '' && (int) ($info['http_code'] ?? 0) < 500,
            'status' => (int) ($info['http_code'] ?? 0),
            'body' => $body,
            'error' => $error,
            'info' => $info,
        ];
    }
}
