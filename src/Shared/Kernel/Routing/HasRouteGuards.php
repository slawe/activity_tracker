<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Routing;

use App\Auth\Presentation\Guards\AdminGuard;
use App\Auth\Presentation\Guards\AuthGuard;
use App\Auth\Presentation\Guards\CsrfGuard;

trait HasRouteGuards
{
    /** @var list<class-string<RouteGuardInterface>> */
    private array $guards = [];

    public function auth(): self
    {
        return $this->guard(AuthGuard::class);
    }

    public function admin(): self
    {
        return $this->guard(AdminGuard::class);
    }

    public function csrf(): self
    {
        return $this->guard(CsrfGuard::class);
    }

    /**
     * @param class-string<RouteGuardInterface> $guardClass
     */
    public function guard(string $guardClass): self
    {
        if (!in_array($guardClass, $this->guards, true)) {
            $this->guards[] = $guardClass;
        }

        return $this;
    }

    /**
     * @return list<class-string<RouteGuardInterface>>
     */
    public function guards(): array
    {
        return $this->guards;
    }
}
