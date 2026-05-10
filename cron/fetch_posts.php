<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\CronLock;
use App\services\CommentService;

$lock = new CronLock('fetch_posts');
if (!$lock->acquire()) {
    echo '[' . date('c') . '] fetch_posts is already running.' . PHP_EOL;
    exit(0);
}

try {
    $result = (new CommentService())->fetchPosts();
    echo '[' . date('c') . '] created=' . $result['created'] . ', skipped=' . $result['skipped'] . PHP_EOL;
} finally {
    $lock->release();
}
