<?php

return [
    'bot' => [
        'enabled' => env_bool('BOT_ENABLED', true),
        'batch_limit' => env_int('BOT_BATCH_LIMIT', 5),
        'min_delay_seconds' => env_int('BOT_MIN_DELAY_SECONDS', 20),
        'max_delay_seconds' => env_int('BOT_MAX_DELAY_SECONDS', 90),
        'cooldown_seconds' => env_int('BOT_COOLDOWN_SECONDS', 900),
        'lock_ttl_seconds' => env_int('BOT_LOCK_TTL_SECONDS', 900),
        'default_user_agent' => env('BOT_DEFAULT_USER_AGENT', 'Mozilla/5.0'),
    ],
    'facebook' => [
        'base_url' => rtrim((string) env('FACEBOOK_BASE_URL', 'https://mbasic.facebook.com'), '/'),
        'comment_endpoint' => env('FACEBOOK_COMMENT_ENDPOINT', 'https://mbasic.facebook.com/a/comment.php'),
        'cookie_file' => base_path((string) env('FACEBOOK_COOKIE_FILE', 'storage/cookies/facebook.cookie')),
        'user_agent' => env('FACEBOOK_USER_AGENT', ''),
    ],
    'telegram' => [
        'enabled' => env_bool('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', ''),
    ],
    'logging' => [
        'level' => env('LOG_LEVEL', 'info'),
        'file' => storage_path('logs/app.log'),
    ],
];
