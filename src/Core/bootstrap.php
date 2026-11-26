<?php

declare(strict_types=1);

use App\Core\Database;

$root = dirname(__DIR__, 2);
$configFile = $root . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Конфигуратсияи TRC ёфт нашуд. config/config.example.php-ро ба config/config.php нусха кунед.');
}
$config = require $configFile;
if (!is_array($config) || !isset($config['app'], $config['db'])) {
    http_response_code(503);
    exit('Сохтори конфигуратсияи TRC нодуруст аст.');
}

spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $file = $root . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

date_default_timezone_set((string) ($config['app']['timezone'] ?? 'Asia/Dushanbe'));
Database::configure($config['db']);

$security = $config['security'] ?? [];
session_name((string) ($security['session_name'] ?? 'TRCSESSID'));
$basePath = trim((string) ($config['app']['base_path'] ?? ''));
$cookiePath = $basePath === '' ? '/' : '/' . trim($basePath, '/') . '/';
session_set_cookie_params([
    'lifetime' => (int) ($security['session_lifetime'] ?? 0),
    'path' => $cookiePath,
    'secure' => (bool) ($security['session_secure'] ?? false),
    'httponly' => (bool) ($security['session_httponly'] ?? true),
    'samesite' => (string) ($security['session_samesite'] ?? 'Lax'),
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$GLOBALS['trc_config'] = $config;
