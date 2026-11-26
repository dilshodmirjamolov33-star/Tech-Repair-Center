<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\UserRepository;

final class AuthController
{
    public function __construct(private UserRepository $users = new UserRepository()) {}

    public function me(Request $request, array $params = []): never
    {
        ApiResponse::success(['user' => Auth::user(), 'csrf_token' => Csrf::token()]);
    }

    public function login(Request $request, array $params = []): never
    {
        Csrf::verify();
        $username = Validator::requiredText($request->input('username'), 50);
        $password = $request->input('password');
        if ($username === null || !is_string($password) || $password === '') {
            ApiResponse::error('validation_error', 'Номи корбар ва рамзро ворид кунед.', 422);
        }
        $user = $this->users->findActiveByUsername($username);
        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            ApiResponse::error('invalid_credentials', 'Номи корбар ё рамз нодуруст аст.', 401);
        }
        Auth::login((int) $user['id']);
        ApiResponse::success(['user' => Auth::user()]);
    }

    public function logout(Request $request, array $params = []): never
    {
        Auth::requireRoles(['admin', 'receptionist', 'master']);
        Csrf::verify();
        Auth::logout();
        ApiResponse::success(['message' => 'Сессия анҷом ёфт.']);
    }
}
