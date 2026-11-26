<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\PartRepository;

final class PartsController
{
    public function __construct(private PartRepository $parts = new PartRepository()) {}

    public function index(Request $request, array $params = []): never
    {
        Auth::requireRoles(['admin', 'receptionist', 'master']);
        $search = Validator::requiredText($request->query('q', ''), 100) ?? '';
        $lowStock = filter_var($request->query('low_stock', false), FILTER_VALIDATE_BOOLEAN);
        ApiResponse::success($this->parts->list($search, $lowStock));
    }
}
