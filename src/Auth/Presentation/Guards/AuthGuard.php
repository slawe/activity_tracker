<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Guards;

use App\Auth\Application\CurrentUserProvider;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Routing\RouteGuardInterface;

final class AuthGuard implements RouteGuardInterface
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
    ) {
    }

    public function check(Request $request): ?Response
    {
        return $this->currentUserProvider->get() === null
            ? new RedirectResponse('/login')
            : null;
    }
}
