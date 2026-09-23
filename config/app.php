<?php

return [
    'name' => env('APP_NAME', 'WACM'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => rtrim(env('APP_URL', 'http://localhost:8000'), '/'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    
    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 120), // minutes
        'secure' => (bool) env('SESSION_SECURE_COOKIE', false),
        'httponly' => true,
        'samesite' => env('SESSION_SAME_SITE', 'Lax'),
    ],
    
    'security' => [
        'csrf_token_name' => '_csrf_token',
        'csrf_header_name' => 'X-CSRF-TOKEN',
    ]
];
