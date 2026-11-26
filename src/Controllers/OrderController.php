<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;

final class OrderController
{
    public function __construct(
        private OrderRepository $orders = new OrderRepository(),
        private OrderStatusRepository $history = new OrderStatusRepository()
    ) {}

    public function index(Request $request, array $params = []): never
    {
        $user = Auth::requireRoles(['admin', 'receptionist', 'master']);
        $filters = [
            'status' => Validator::orderStatus($request->query('status')),
            'q' => Validator::requiredText($request->query('q', ''), 100) ?? '',
        ];
        if ($user['role'] === 'master') $filters['master_id'] = (int) $user['id'];
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        ApiResponse::success($this->orders->list($filters, $page, $perPage));
    }

    public function show(Request $request, array $params = []): never
    {
        $user = Auth::requireRoles(['admin', 'receptionist', 'master']);
        $id = Validator::positiveId($params['id'] ?? null);
        if ($id === null) ApiResponse::error('invalid_id', 'Рақами фармоиш нодуруст аст.', 422);
        $order = $this->orders->findById($id);
        if (!$order) ApiResponse::error('not_found', 'Фармоиш ёфт нашуд.', 404);
        if ($user['role'] === 'master' && (int) ($order['master_id'] ?? 0) !== (int) $user['id']) {
            ApiResponse::error('forbidden', 'Ин фармоиш ба шумо таъин нашудааст.', 403);
        }
        ApiResponse::success(['order' => $order, 'history' => $this->history->history($id)]);
    }

    public function updateStatus(Request $request, array $params = []): never
    {
        $user = Auth::requireRoles(['admin', 'master']);
        Csrf::verify();
        $id = Validator::positiveId($params['id'] ?? null);
        $status = Validator::orderStatus($request->input('status'));
        if ($id === null || $status === null) ApiResponse::error('validation_error', 'Рақами фармоиш ё ҳолат нодуруст аст.', 422);
        $order = $this->orders->findById($id);
        if (!$order) ApiResponse::error('not_found', 'Фармоиш ёфт нашуд.', 404);
        if ($user['role'] === 'master' && (int) ($order['master_id'] ?? 0) !== (int) $user['id']) {
            ApiResponse::error('forbidden', 'Ин фармоиш ба шумо таъин нашудааст.', 403);
        }
        $allowedNext = [
            'received' => ['diagnosis'],
            'diagnosis' => ['repair', 'ready'],
            'repair' => ['ready'],
            'ready' => ['repair', 'delivered'],
            'delivered' => [],
        ];
        if (!in_array($status, $allowedNext[$order['status']] ?? [], true) && $status !== $order['status']) {
            ApiResponse::error('invalid_transition', 'Гузариш ба ин ҳолат иҷозат дода намешавад.', 409);
        }
        $note = Validator::requiredText($request->input('note', ''), 255);
        $this->orders->changeStatus($id, $status, $note, (int) $user['id']);
        ApiResponse::success(['message' => 'Ҳолати фармоиш нав карда шуд.']);
    }
}
