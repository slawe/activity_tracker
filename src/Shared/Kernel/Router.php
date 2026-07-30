<?php

declare(strict_types=1);

namespace App\Shared\Kernel;

use App\Shared\Kernel\Http\HtmlResponse;

final class Router
{
    /** @var array<string, callable(Request): Response> */
    private array $routes = [];

    /**
     * @param callable(Request): Response $handler
     */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$this->key($request->method(), $request->path())] ?? null;

        if ($handler === null) {
            return new HtmlResponse('<h1>404 Not Found</h1>', 404);
        }

        return $handler($request);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[$this->key($method, $path)] = $handler;
    }

    private function key(string $method, string $path): string
    {
        return sprintf('%s %s', $method, $path);
    }
}
