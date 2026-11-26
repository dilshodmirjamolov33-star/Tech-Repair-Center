<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function configure(array $config): void
    {
        $driver = (string) ($config['driver'] ?? 'mysql');
        if ($driver !== 'mysql') {
            throw new RuntimeException('Танҳо драйвери MySQL дастгирӣ мешавад.');
        }

        foreach (['host', 'database', 'user'] as $key) {
            if (trim((string) ($config[$key] ?? '')) === '') {
                throw new RuntimeException("Танзимоти DB барои {$key} пур карда нашудааст.");
            }
        }

        $host = (string) $config['host'];
        $port = (int) ($config['port'] ?? 3306);
        $name = (string) $config['database'];
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, (string) $config['user'], (string) ($config['pass'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('TRC database connection error: ' . $exception->getMessage());
            throw new RuntimeException('Пайвастшавӣ ба пойгоҳи додаҳо дастнорас аст.');
        }
    }

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            throw new RuntimeException('Пайвасти пойгоҳи додаҳо ҳанӯз танзим нашудааст.');
        }
        return self::$connection;
    }
}
