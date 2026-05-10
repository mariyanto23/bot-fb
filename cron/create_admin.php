<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\models\Admin;

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$name = $argv[3] ?? 'Administrator';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    echo 'Usage: php cron/create_admin.php admin@example.com strong-password [name]' . PHP_EOL;
    exit(1);
}

(new Admin())->ensureDefaultAdmin($email, $password, $name);
echo 'Admin is ready: ' . $email . PHP_EOL;
