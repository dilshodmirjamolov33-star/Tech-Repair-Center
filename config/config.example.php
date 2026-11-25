<?php

declare(strict_types=1);

/**
 * Намунаи конфигуратсияи Tech Repair Center.
 * Ин файл намунавӣ аст; конфигуратсияи фаъоли config.php-ро
 * бе санҷиши роҳҳо ва муҳити хостинг иваз накунед.
 */

$env = static function (string $key, mixed $default = null): mixed {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

$environment = strtolower((string) $env('APP_ENV', 'production'));
if (!in_array($environment, ['production', 'development', 'testing'], true)) {
    $environment = 'production';
}

return [
    'app' => [
        'name' => 'Tech Repair Center',
        'env' => $environment,
        'debug' => filter_var($env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOLEAN),
        'timezone' => 'Asia/Dushanbe',
        'currency' => 'сомонӣ',
    ],

    'db' => [
        'driver' => 'mysql',
        'host' => (string) $env('DB_HOST', 'localhost'),
        'port' => (int) $env('DB_PORT', 3306),
        'database' => (string) $env('DB_NAME', 'repair_center'),
        'user' => (string) $env('DB_USER', 'root'),
        'pass' => (string) $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],

    'security' => [
        'session_name' => 'TRCSESSID',
        'session_lifetime' => 0,
        'session_secure' => filter_var($env('SESSION_SECURE', '0'), FILTER_VALIDATE_BOOLEAN),
        'session_httponly' => true,
        'session_samesite' => 'Lax',
        'csrf_bytes' => 32,
    ],

    'orders' => [
        'statuses' => ['received', 'diagnosis', 'repair', 'ready', 'delivered'],
        'warranty_default_days' => 0,
    ],

    'uploads' => [
        'directory' => dirname(__DIR__) . '/uploads',
        'max_file_size' => 5 * 1024 * 1024,
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
