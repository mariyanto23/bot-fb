<?php

use App\Core\Csrf;
use App\Core\Session;

$user = Session::user();
$success = Session::flash('success');
$error = Session::flash('error');
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= e(url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg border-bottom bg-body">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?= e(url('/dashboard')) ?>"><?= e(config('app.name')) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ([
                    '/dashboard' => ['Dashboard', 'bi-speedometer2'],
                    '/comments' => ['Comments', 'bi-chat-text'],
                    '/targets' => ['Targets', 'bi-bullseye'],
                    '/bot' => ['Bot', 'bi-robot'],
                    '/logs' => ['Logs', 'bi-journal-text'],
                    '/settings' => ['Settings', 'bi-sliders'],
                ] as $path => $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($current, $path) ? 'active' : '' ?>" href="<?= e(url($path)) ?>">
                            <i class="bi <?= e($item[1]) ?>"></i> <?= e($item[0]) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-secondary"><?= e($user['name'] ?? '') ?></span>
                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= Csrf::field() ?>
                    <button class="btn btn-outline-secondary btn-sm" type="submit" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
    <?php if ($success): ?>
        <div class="toast align-items-center text-bg-success border-0 show app-toast" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= e($success) ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="toast align-items-center text-bg-danger border-0 show app-toast" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= e($error) ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= e(url('/assets/js/app.js')) ?>"></script>
</body>
</html>
