<?php

use App\controllers\AuthController;
use App\controllers\BotController;
use App\controllers\CommentController;
use App\controllers\DashboardController;
use App\controllers\LogController;
use App\controllers\SettingController;
use App\controllers\TargetController;

$router->get('/', [DashboardController::class, 'index'], ['auth']);

$router->get('/login', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard/stats', [DashboardController::class, 'stats'], ['auth']);

$router->get('/comments', [CommentController::class, 'index'], ['auth']);
$router->post('/comments', [CommentController::class, 'store'], ['auth', 'csrf']);
$router->post('/comments/update', [CommentController::class, 'update'], ['auth', 'csrf']);
$router->post('/comments/delete', [CommentController::class, 'delete'], ['auth', 'csrf']);

$router->get('/targets', [TargetController::class, 'index'], ['auth']);
$router->post('/targets', [TargetController::class, 'store'], ['auth', 'csrf']);
$router->post('/targets/update', [TargetController::class, 'update'], ['auth', 'csrf']);
$router->post('/targets/delete', [TargetController::class, 'delete'], ['auth', 'csrf']);

$router->get('/settings', [SettingController::class, 'index'], ['auth']);
$router->post('/settings', [SettingController::class, 'update'], ['auth', 'csrf']);

$router->get('/logs', [LogController::class, 'index'], ['auth']);

$router->get('/bot', [BotController::class, 'index'], ['auth']);
$router->post('/bot/run', [BotController::class, 'run'], ['auth', 'csrf']);
$router->post('/bot/fetch-posts', [BotController::class, 'fetchPosts'], ['auth', 'csrf']);
$router->post('/bot/send-comments', [BotController::class, 'sendComments'], ['auth', 'csrf']);
$router->post('/bot/cookie', [BotController::class, 'saveCookie'], ['auth', 'csrf']);
$router->post('/bot/cookie/clear', [BotController::class, 'clearCookie'], ['auth', 'csrf']);
