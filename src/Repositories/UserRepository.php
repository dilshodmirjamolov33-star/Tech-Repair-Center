<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    public function findActiveByUsername(string $username): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, full_name, username, password_hash, role, is_active FROM users WHERE username = :username AND is_active = 1 LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listMasters(): array
    {
        $stmt = Database::connection()->query("SELECT id, full_name FROM users WHERE role = 'master' AND is_active = 1 ORDER BY full_name");
        return $stmt->fetchAll();
    }
}
