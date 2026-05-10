<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    require dirname(__DIR__) . '/app/helpers/functions.php';
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

use App\Core\App;
use App\models\Admin;

App::bootstrap();

$token = (string) env('INSTALL_ADMIN_TOKEN', '');
$requestToken = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

if ($token === '' || !hash_equals($token, $requestToken)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $name = trim((string) ($_POST['name'] ?? 'Administrator'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        try {
            (new Admin())->ensureDefaultAdmin($email, $password, $name ?: 'Administrator');
            $message = 'Admin berhasil dibuat. Hapus file public/install_admin.php atau kosongkan INSTALL_ADMIN_TOKEN sekarang.';
        } catch (Throwable $exception) {
            $error = 'Gagal membuat admin: ' . $exception->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<main class="container py-5" style="max-width: 520px">
    <h1 class="h3 mb-3">Setup Admin</h1>
    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="card card-body bg-body border-secondary">
        <input type="hidden" name="token" value="<?= e($requestToken) ?>">
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input class="form-control" name="name" value="Administrator">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" name="password" type="password" minlength="8" required>
        </div>
        <button class="btn btn-primary" type="submit">Buat Admin</button>
    </form>
</main>
</body>
</html>
