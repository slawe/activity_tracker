<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Guards;

use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Routing\RouteGuardInterface;
use App\Shared\Kernel\Security\CsrfTokenManager;

final class CsrfGuard implements RouteGuardInterface
{
    public function __construct(
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function check(Request $request): ?Response
    {
        return $this->csrf->isValid($request->postString('_csrf'))
            ? null
            : new HtmlResponse('<h1>419 Page Expired</h1>', 419);
    }
}
