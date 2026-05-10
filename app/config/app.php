<?php

return [
    'name' => env('APP_NAME', 'FB Affiliate Comment Bot'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env_bool('APP_DEBUG', false),
    'url' => rtrim((string) env('APP_URL', ''), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'session_name' => env('SESSION_NAME', 'fb_comment_bot_session'),
    'session_lifetime' => env_int('SESSION_LIFETIME', 7200),
];
