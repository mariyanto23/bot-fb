<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\CronLock;
use App\services\CommentService;

$lock = new CronLock('send_comments');
if (!$lock->acquire()) {
    echo '[' . date('c') . '] send_comments is already running.' . PHP_EOL;
    exit(0);
}

try {
    $result = (new CommentService())->sendPendingComments();
    echo '[' . date('c') . '] ' . $result['message'] . PHP_EOL;
} finally {
    $lock->release();
}
