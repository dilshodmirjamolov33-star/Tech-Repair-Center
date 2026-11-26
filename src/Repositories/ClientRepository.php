<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ClientRepository
{
    public function findByPhone(string $phone): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, full_name, phone, created_at FROM clients WHERE phone = :phone LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
