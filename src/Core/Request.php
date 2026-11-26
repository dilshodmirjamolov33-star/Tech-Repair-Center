<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return '/' . trim(rawurldecode($path), '/');
    }
    public function query(string $key, mixed $default = null): mixed { return $_GET[$key] ?? $default; }
    public function input(string $key, mixed $default = null): mixed
    {
        $body = $this->jsonBody();
        return $_POST[$key] ?? $body[$key] ?? $default;
    }
    public function jsonBody(): array
    {
        static $decoded = null;
        if ($decoded !== null) return $decoded;
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') return $decoded = [];
        $value = json_decode($raw, true);
        return $decoded = is_array($value) ? $value : [];
    }
}
