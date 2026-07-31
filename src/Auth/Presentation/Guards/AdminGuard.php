<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Guards;

use App\Auth\Application\CurrentUserProvider;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Routing\RouteGuardInterface;

final class AdminGuard implements RouteGuardInterface
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
    ) {
    }

    public function check(Request $request): ?Response
    {
        $user = $this->currentUserProvider->get();
        if ($user === null) {
            return new RedirectResponse('/login');
        }
        if (!$user->isAdmin()) {
            return new HtmlResponse('<h1>403 Forbidden</h1>', 403);
        }

        return null;
    }
}
