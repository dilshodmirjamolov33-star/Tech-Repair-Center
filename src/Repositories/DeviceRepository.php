<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class DeviceRepository
{
    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, client_id, type, brand, model, serial_no, created_at FROM devices WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
