<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $quoted = preg_replace_callback('/\\{([a-zA-Z_][a-zA-Z0-9_]*)\\}/', static fn(array $m) => '(?P<' . $m[1] . '>[^/]+)', preg_quote($pattern, '#'));
        $quoted = str_replace(['\\(', '\\)', '\\?P', '\\<', '\\>'], ['(', ')', '?P', '<', '>'], (string) $quoted);
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => '#^' . $quoted . '/?$#', 'handler' => $handler];
    }

    public function dispatch(Request $request): never
    {
        $path = $request->path();
        $path = $path === '/' ? '/' : rtrim($path, '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) continue;
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                ($route['handler'])($request, $params);
                exit;
            }
        }
        ApiResponse::error('not_found', 'Масири дархостшуда ёфт нашуд.', 404);
    }
}
