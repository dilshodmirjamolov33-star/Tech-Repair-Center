<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['_csrf'];
    }

    public static function verify(): void
    {
        $expected = (string) ($_SESSION['_csrf'] ?? '');
        $actual = (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($expected === '' || $actual === '' || !hash_equals($expected, $actual)) {
            ApiResponse::error('csrf_mismatch', 'Токени амният нодуруст ё нест.', 419);
        }
    }
}
