<?php

return [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env_int('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'fb_comment_bot'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
