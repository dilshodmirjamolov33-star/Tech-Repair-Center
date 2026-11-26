<?php

declare(strict_types=1);

namespace App\Core;

final class ApiResponse
{
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    public static function success(mixed $data = null, int $status = 200, array $meta = []): never
    {
        self::json(['ok' => true, 'data' => $data, 'meta' => (object) $meta], $status);
    }

    public static function error(string $code, string $message, int $status, array $details = []): never
    {
        self::json(['ok' => false, 'error' => ['code' => $code, 'message' => $message, 'details' => (object) $details]], $status);
    }
}
