<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class OrderRepository
{
    public function findById(int $id): ?array
    {
        $sql = 'SELECT o.*, c.full_name AS client_name, c.phone AS client_phone,
                       d.type AS device_type, d.brand AS device_brand, d.model AS device_model,
                       u.full_name AS master_name
                FROM orders o
                INNER JOIN clients c ON c.id = o.client_id
                INNER JOIN devices d ON d.id = o.device_id
                LEFT JOIN users u ON u.id = o.master_id
                WHERE o.id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'o.status = :status'; $params['status'] = $filters['status']; }
        if (!empty($filters['master_id'])) { $where[] = 'o.master_id = :master_id'; $params['master_id'] = (int) $filters['master_id']; }
        if (!empty($filters['q'])) {
            $where[] = '(o.receipt_no LIKE :q OR c.full_name LIKE :q OR c.phone LIKE :q OR d.brand LIKE :q OR d.model LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count = Database::connection()->prepare("SELECT COUNT(*) FROM orders o INNER JOIN clients c ON c.id=o.client_id INNER JOIN devices d ON d.id=o.device_id {$clause}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT o.id, o.receipt_no, o.status, o.total, o.created_at,
                       c.full_name AS client_name, c.phone AS client_phone,
                       d.type AS device_type, d.brand AS device_brand, d.model AS device_model,
                       u.full_name AS master_name
                FROM orders o
                INNER JOIN clients c ON c.id=o.client_id
                INNER JOIN devices d ON d.id=o.device_id
                LEFT JOIN users u ON u.id=o.master_id
                {$clause} ORDER BY o.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return ['items' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function changeStatus(int $orderId, string $status, ?string $note, ?int $userId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $lock = $pdo->prepare('SELECT status FROM orders WHERE id = :id FOR UPDATE');
            $lock->execute(['id' => $orderId]);
            if (!$lock->fetch()) throw new \RuntimeException('Фармоиш ёфт нашуд.');
            $update = $pdo->prepare('UPDATE orders SET status = :status, delivered_at = CASE WHEN :status = \'delivered\' THEN NOW() ELSE delivered_at END WHERE id = :id');
            $update->execute(['status' => $status, 'id' => $orderId]);
            $log = $pdo->prepare('INSERT INTO order_status_log (order_id, status, note, user_id) VALUES (:order_id, :status, :note, :user_id)');
            $log->execute(['order_id' => $orderId, 'status' => $status, 'note' => $note, 'user_id' => $userId]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}
