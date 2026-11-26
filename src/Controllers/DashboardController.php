<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;

final class DashboardController
{
    public function index(Request $request, array $params = []): never
    {
        Auth::requireRoles(['admin', 'receptionist', 'master']);
        $pdo = Database::connection();
        $summary = [
            'orders_total' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'orders_active' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('received','diagnosis','repair')")->fetchColumn(),
            'orders_ready' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ready'")->fetchColumn(),
            'clients_total' => (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn(),
            'parts_low_stock' => (int) $pdo->query('SELECT COUNT(*) FROM parts WHERE stock <= min_stock')->fetchColumn(),
        ];
        ApiResponse::success($summary);
    }
}
