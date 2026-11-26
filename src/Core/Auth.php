<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private static bool $loaded = false;
    private static ?array $current = null;

    public static function user(): ?array
    {
        if (self::$loaded) return self::$current;
        self::$loaded = true;
        $id = Validator::positiveId($_SESSION['u']['id'] ?? null);
        if ($id === null) return null;
        $stmt = Database::connection()->prepare('SELECT id, full_name, username, role, is_active FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || (int) $user['is_active'] !== 1) return null;
        self::$current = $user;
        return self::$current;
    }

    public static function requireRoles(array $roles): array
    {
        $user = self::user();
        if ($user === null) ApiResponse::error('unauthenticated', 'Барои иҷрои ин амал ворид шавед.', 401);
        if (!in_array($user['role'], $roles, true)) ApiResponse::error('forbidden', 'Шумо иҷозати иҷрои ин амалро надоред.', 403);
        return $user;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['u'] = ['id' => $userId];
        self::$loaded = false;
        self::$current = null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$loaded = false;
        self::$current = null;
    }
}
