<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\models\Setting;

final class SettingController extends Controller
{
    private array $allowedKeys = [
        'bot_enabled',
        'bot_batch_limit',
        'bot_min_delay_seconds',
        'bot_max_delay_seconds',
        'bot_cooldown_seconds',
        'facebook_base_url',
        'facebook_user_agent',
        'telegram_enabled',
        'telegram_bot_token',
        'telegram_chat_id',
    ];

    public function index(): void
    {
        $this->view('settings/index', [
            'title' => 'Settings',
            'settings' => (new Setting())->allAssoc(),
        ]);
    }

    public function update(Request $request): void
    {
        $payload = [];
        foreach ($this->allowedKeys as $key) {
            $payload[$key] = trim((string) $request->input($key, ''));
        }

        $payload['bot_enabled'] = $request->input('bot_enabled') ? '1' : '0';
        $payload['telegram_enabled'] = $request->input('telegram_enabled') ? '1' : '0';

        (new Setting())->setMany($payload);
        Session::flash('success', 'Settings saved.');
        redirect('/settings');
    }
}
