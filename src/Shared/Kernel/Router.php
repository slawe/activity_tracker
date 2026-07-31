<?php

declare(strict_types=1);

namespace App\Shared\Kernel;

use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Routing\Route;
use App\Shared\Kernel\Routing\RouteGuardRunner;

final class Router
{
    /** @var array<string, Route> */
    private array $routes = [];

    public function __construct(
        private readonly RouteGuardRunner $guardRunner,
    ) {
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function get(string $path, callable $handler): Route
    {
        return $this->add('GET', $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function post(string $path, callable $handler): Route
    {
        return $this->add('POST', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $route = $this->routes[$this->key($request->method(), $request->path())] ?? null;

        if ($route === null) {
            return new HtmlResponse('<h1>404 Not Found</h1>', 404);
        }

        return $this->guardRunner->check($route, $request) ?? $route->run($request);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function add(string $method, string $path, callable $handler): Route
    {
        $route = new Route($method, $path, $handler);
        $this->routes[$this->key($method, $path)] = $route;

        return $route;
    }

    private function key(string $method, string $path): string
    {
        return sprintf('%s %s', $method, $path);
    }
}
