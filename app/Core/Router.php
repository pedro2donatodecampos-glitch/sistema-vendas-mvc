<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array{0: string, 1: string, 2: string}> */
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = preg_replace('/\{id\}/', '([0-9]+)', $pattern);
            if (preg_match('#^' . $regex . '/?$#', $path, $matches)) {
                [$controllerName, $action] = explode('@', $handler);
                $controller = new $controllerName();
                $controller->$action(...array_slice($matches, 1));
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Página não encontrada']);
    }

    private function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [$method, $path, $controller . '@' . $action];
    }
}
