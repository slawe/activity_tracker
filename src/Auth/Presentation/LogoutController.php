<?php

declare(strict_types=1);

namespace App\Auth\Presentation;

use App\Auth\Application\LogoutUserHandler;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;

final class LogoutController
{
    public function __construct(
        private readonly LogoutUserHandler $handler,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function submit(Request $request): Response
    {
        try {
            $this->handler->handle($request->ipAddress(), $request->userAgent());
        } finally {
            $this->csrf->refresh();
        }

        return new RedirectResponse('/login');
    }
}
