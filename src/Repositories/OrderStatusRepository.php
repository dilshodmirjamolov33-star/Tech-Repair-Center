<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class OrderStatusRepository
{
    public function history(int $orderId): array
    {
        $stmt = Database::connection()->prepare('SELECT l.id, l.status, l.note, l.created_at, u.full_name AS changed_by FROM order_status_log l LEFT JOIN users u ON u.id = l.user_id WHERE l.order_id = :order_id ORDER BY l.created_at, l.id');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }
}
