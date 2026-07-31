<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Routing;

use App\Shared\Kernel\Container;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use LogicException;

final class RouteGuardRunner
{
    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function check(Route $route, Request $request): ?Response
    {
        foreach ($route->guards() as $guardClass) {
            $guard = $this->container->get($guardClass);
            if (!$guard instanceof RouteGuardInterface) {
                throw new LogicException(sprintf('Route guard "%s" must implement RouteGuardInterface.', $guardClass));
            }

            $response = $guard->check($request);
            if ($response !== null) {
                return $response;
            }
        }

        return null;
    }
}
