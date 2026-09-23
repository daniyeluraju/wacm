<?php

return [
    'paths' => [
        'root' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/storage/uploads',
        'temporary' => dirname(__DIR__) . '/storage/temporary',
        'exports' => dirname(__DIR__) . '/storage/exports',
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
    
    'uploads' => [
        'max_size_bytes' => (int) env('UPLOAD_MAX_SIZE', 10485760), // 10MB default
        'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_image_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'allowed_import_mimes' => [
            'text/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'allowed_import_extensions' => ['csv', 'xlsx'],
    ],
    
    'cleanup' => [
        'enabled' => (bool) env('CLEANUP_ENABLED', true),
        'retention_hours' => (int) env('CLEANUP_RETENTION_HOURS', 24),
        'auto_delete_temp' => (bool) env('CLEANUP_AUTO_DELETE_TEMP', true),
        'warning_threshold_mb' => (int) env('STORAGE_WARNING_THRESHOLD_MB', 500),
    ],
];
