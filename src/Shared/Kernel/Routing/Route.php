<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Routing;

use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;

final class Route
{
    use HasRouteGuards;

    /**
     * @param callable(Request): Response $handler
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private $handler,
    ) {
    }

    public function run(Request $request): Response
    {
        return ($this->handler)($request);
    }
}
