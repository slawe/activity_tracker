<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Routing;

use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;

interface RouteGuardInterface
{
    public function check(Request $request): ?Response;
}
