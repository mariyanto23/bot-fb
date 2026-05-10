<?php

namespace App\services;

use App\helpers\CurlHelper;
use App\models\TelegramLog;

final class TelegramService
{
    public function __construct(
        private ?TelegramLog $logs = null,
        private ?SettingService $settings = null
    ) {
        $this->logs ??= new TelegramLog();
        $this->settings ??= new SettingService();
    }

    public function send(string $message): bool
    {
        if (!$this->settings->bool('telegram_enabled', (bool) config('config.telegram.enabled', false))) {
            return false;
        }

        $token = $this->settings->string('telegram_bot_token', config('config.telegram.bot_token', ''));
        $chatId = $this->settings->string('telegram_chat_id', config('config.telegram.chat_id', ''));
        if ($token === '' || $chatId === '') {
            $this->logs->create('failed', $message, 'Telegram token or chat id is empty.');
            return false;
        }

        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $response = CurlHelper::request($url, [
            'post' => http_build_query([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => '1',
            ]),
            'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $status = $response['ok'] ? 'success' : 'failed';
        $this->logs->create($status, $message, $response['body'] ?: $response['error']);

        return $response['ok'];
    }
}
