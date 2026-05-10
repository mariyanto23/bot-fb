<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\models\BotStatus;
use App\models\Log;
use App\models\Post;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => (new Post())->stats(),
            'posts' => (new Post())->latest(10),
            'logs' => (new Log())->latest(10),
            'statuses' => (new BotStatus())->all(),
        ]);
    }

    public function stats(Request $request): void
    {
        $this->json([
            'stats' => (new Post())->stats(),
            'logs' => (new Log())->latest(10),
            'statuses' => (new BotStatus())->all(),
        ]);
    }
}
