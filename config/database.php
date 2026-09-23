<?php

$dbUrl = env('DATABASE_URL', env('MYSQL_URL'));
$urlHost = null;
$urlPort = null;
$urlDatabase = null;
$urlUser = null;
$urlPass = null;

if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    if ($parsed) {
        $urlHost = $parsed['host'] ?? null;
        $urlPort = $parsed['port'] ?? null;
        $urlDatabase = !empty($parsed['path']) ? ltrim($parsed['path'], '/') : null;
        $urlUser = $parsed['user'] ?? null;
        $urlPass = $parsed['pass'] ?? null;
    }
}

return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $urlHost ?? env('DB_HOST', '127.0.0.1'),
            'port' => $urlPort ?? env('DB_PORT', '3306'),
            'database' => $urlDatabase ?? env('DB_DATABASE', 'wacm'),
            'username' => $urlUser ?? env('DB_USERNAME', 'root'),
            'password' => $urlPass ?? env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
            ],
        ],
    ],
];
