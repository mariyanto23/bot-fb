<?php

namespace App\controllers;

use App\Core\Controller;
use App\models\Log;
use App\models\TelegramLog;

final class LogController extends Controller
{
    public function index(): void
    {
        $this->view('logs/index', [
            'title' => 'Logs',
            'logs' => (new Log())->latest(200),
            'telegramLogs' => (new TelegramLog())->latest(100),
        ]);
    }
}
