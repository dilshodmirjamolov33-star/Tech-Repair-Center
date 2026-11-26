<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class PartRepository
{
    public function list(string $search = '', bool $lowStockOnly = false): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE :q OR sku LIKE :q)';
            $params['q'] = '%' . $search . '%';
        }
        if ($lowStockOnly) $where[] = 'stock <= min_stock';
        $sql = 'SELECT id, name, sku, price, stock, min_stock FROM parts';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
