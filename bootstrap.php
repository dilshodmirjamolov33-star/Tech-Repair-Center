<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Боркунаки асосии Tech Repair Center (TRC).
 *
 * Вазифаҳо:
 *  - хондани конфигуратсияи хусусӣ аз config/config.php;
 *  - санҷидани танзимоти барнома ва MySQL;
 *  - сабти танзимот дар $GLOBALS['trc_config'];
 *  - таъини минтақаи вақт;
 *  - автоборкунии синфҳои App\\ аз папкаи app/;
 *  - оғоз кардани сессияи бехатар;
 *  - танзими пайвастшавӣ ба базаи маълумот.
 *
 * Эзоҳ: ин файл config/config.example.php-ро мустақим истифода намекунад.
 * Аввал онро ба config/config.php нусхабардорӣ карда, маълумоти муҳитро пур кунед.
 */

$projectRoot = dirname(__DIR__, 2);
$configFile = $projectRoot . '/config/config.php';

/**
 * Хатои умумии bootstrap-ро бе ошкор кардани маълумоти дохилӣ нишон медиҳад.
 */
$trcBootstrapFail = static function (
    int $status,
    string $errorCode,
    string $message
): never {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    exit($message . ' [' . $errorCode . ']');
};

if (!is_file($configFile) || !is_readable($configFile)) {
    $trcBootstrapFail(
        503,
        'configuration_missing',
        'Конфигуратсияи TRC ёфт нашуд. Файли config/config.example.php-ро ба config/config.php нусхабардорӣ кунед.'
    );
}

try {
    $config = require $configFile;
} catch (Throwable $exception) {
    error_log('TRC configuration loading failed: ' . $exception->getMessage());
    $trcBootstrapFail(
        503,
        'configuration_load_failed',
        'Файли конфигуратсияи TRC хонда нашуд.'
    );
}

if (
    !is_array($config)
    || !isset($config['app'], $config['db'])
    || !is_array($config['app'])
    || !is_array($config['db'])
) {
    $trcBootstrapFail(
        503,
        'configuration_invalid',
        'Сохтори конфигуратсияи TRC нодуруст аст.'
    );
}

/** Муайян кардани муҳити барнома. */
$environment = strtolower((string) ($config['app']['env'] ?? 'production'));
if (!in_array($environment, ['production', 'development', 'testing'], true)) {
    $environment = 'production';
}

$debug = (bool) ($config['app']['debug'] ?? false);
if ($environment === 'production') {
    $debug = false;
}

$config['app']['env'] = $environment;
$config['app']['debug'] = $debug;

ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

/** Санҷиши танзимоти MySQL. */
$dbConfig = $config['db'];
$driver = (string) ($dbConfig['driver'] ?? 'mysql');
if ($driver !== 'mysql') {
    $trcBootstrapFail(
        503,
        'database_driver_unsupported',
        'Барои Tech Repair Center танҳо MySQL дастгирӣ мешавад.'
    );
}

foreach (['host', 'database', 'user'] as $key) {
    $value = trim((string) ($dbConfig[$key] ?? ''));
    if (
        $value === ''
        || str_contains($value, 'YOUR_DATABASE_')
        || str_contains($value, 'YOUR_INFINITYFREE_')
    ) {
        $trcBootstrapFail(
            503,
            'database_not_configured',
            'Танзимоти MySQL дар config/config.php пур карда нашудааст.'
        );
    }
}

$dbConfig['driver'] = 'mysql';
$dbConfig['port'] = (int) ($dbConfig['port'] ?? 3306);
$dbConfig['charset'] = (string) ($dbConfig['charset'] ?? 'utf8mb4');

if ($dbConfig['port'] < 1 || $dbConfig['port'] > 65535) {
    $trcBootstrapFail(
        503,
        'database_port_invalid',
        'Порти пайвастшавӣ ба MySQL нодуруст аст.'
    );
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbConfig['charset'])) {
    $trcBootstrapFail(
        503,
        'database_charset_invalid',
        'Рамзгузории базаи маълумот нодуруст аст.'
    );
}

// Пароли холӣ барои муҳити маҳаллии рушд иҷозат дода мешавад.
$dbConfig['pass'] = (string) ($dbConfig['pass'] ?? '');
$config['db'] = $dbConfig;

$GLOBALS['trc_config'] = $config;
$GLOBALS['trc_project_root'] = $projectRoot;

date_default_timezone_set((string) ($config['app']['timezone'] ?? 'Asia/Dushanbe'));

/**
 * Автоборкунии синфҳои барнома.
 * Масалан: App\\Core\\Database -> app/Core/Database.php
 */
spl_autoload_register(
    static function (string $class) use ($projectRoot): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
        $classFile = $projectRoot . '/app/' . $relativePath . '.php';

        if (is_file($classFile) && is_readable($classFile)) {
            require_once $classFile;
        }
    }
);

/** Танзимоти сессияи корбар. */
$security = $config['security'] ?? [];
if (!is_array($security)) {
    $security = [];
}

$sessionName = (string) ($security['session_name'] ?? 'TRCSESSID');
if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $sessionName)) {
    $sessionName = 'TRCSESSID';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent($sentFile, $sentLine)) {
        error_log("TRC session cannot start: headers already sent at {$sentFile}:{$sentLine}");
        $trcBootstrapFail(
            500,
            'session_headers_already_sent',
            'Сессия оғоз нашуд. Пеш аз bootstrap ягон маълумот ба браузер фиристода шудааст.'
        );
    }

    $basePath = trim((string) ($config['app']['base_path'] ?? ''));
    $cookiePath = $basePath === ''
        ? '/'
        : '/' . trim($basePath, '/') . '/';

    $isHttps = isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== ''
        && strtolower((string) $_SERVER['HTTPS']) !== 'off';

    $secureCookie = (bool) ($security['session_secure'] ?? false) || $isHttps;
    $sameSite = (string) ($security['session_samesite'] ?? 'Lax');
    if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
        $sameSite = 'Lax';
    }

    // Муҳофизат аз қабул кардани ID-и сессияи аз ҷониби корбар пешниҳодшуда.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => max(0, (int) ($security['session_lifetime'] ?? 0)),
        'path' => $cookiePath,
        'secure' => $secureCookie,
        'httponly' => (bool) ($security['session_httponly'] ?? true),
        'samesite' => $sameSite,
    ]);

    if (!session_start()) {
        $trcBootstrapFail(
            503,
            'session_start_failed',
            'Сессияи корбар оғоз карда нашуд.'
        );
    }
}

/** Пайвастшавӣ ба база тавассути синфи ягонаи Database. */
try {
    Database::configure($dbConfig);
} catch (Throwable $exception) {
    error_log('TRC database initialization failed: ' . $exception->getMessage());
    $trcBootstrapFail(
        503,
        'database_unavailable',
        'Пайвастшавӣ ба пойгоҳи додаҳои TRC дастрас нест. Танзимоти MySQL-ро санҷед.'
    );
}
