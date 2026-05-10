<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\services\CommentService;

$result = (new CommentService())->runBot();
echo '[' . date('c') . '] ' . $result['message'] . PHP_EOL;
if ($result['fetch'] !== []) {
    echo 'Fetched: created=' . ($result['fetch']['created'] ?? 0) . ', skipped=' . ($result['fetch']['skipped'] ?? 0) . PHP_EOL;
}
if ($result['comments'] !== []) {
    echo 'Comments: sent=' . ($result['comments']['sent'] ?? 0) . ', failed=' . ($result['comments']['failed'] ?? 0) . PHP_EOL;
}
