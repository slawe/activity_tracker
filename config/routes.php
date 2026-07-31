<?php

declare(strict_types=1);

use App\Auth\Application\CurrentUserProvider;
use App\Auth\Presentation\LoginController;
use App\Auth\Presentation\LogoutController;
use App\Auth\Presentation\RegisterController;
use App\Pages\Presentation\PageAController;
use App\Pages\Presentation\PageBController;
use App\Reporting\Presentation\ReportsController;
use App\Reporting\Presentation\StatsController;
use App\Shared\Kernel\Container;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Router;

return static function (Router $router, Container $container): void {
    $router->get('/', static function (Request $request) use ($container): RedirectResponse {
        $provider = $container->get(CurrentUserProvider::class);
        assert($provider instanceof CurrentUserProvider);

        return new RedirectResponse($provider->get() === null ? '/login' : '/page-a');
    });

    $login = $container->get(LoginController::class);
    $register = $container->get(RegisterController::class);
    $logout = $container->get(LogoutController::class);
    $pageA = $container->get(PageAController::class);
    $pageB = $container->get(PageBController::class);
    $stats = $container->get(StatsController::class);
    $reports = $container->get(ReportsController::class);

    $router->get('/login', [$login, 'show']);
    $router->post('/login', [$login, 'submit'])->csrf();
    $router->get('/register', [$register, 'show']);
    $router->post('/register', [$register, 'submit'])->csrf();
    $router->post('/logout', [$logout, 'submit'])->auth()->csrf();
    $router->get('/page-a', [$pageA, 'show'])->auth();
    $router->post('/page-a/buy-cow', [$pageA, 'buy'])->auth()->csrf();
    $router->get('/page-b', [$pageB, 'show'])->auth();
    $router->post('/page-b/download', [$pageB, 'download'])->auth()->csrf();
    $router->get('/admin/stats', [$stats, 'show'])->admin();
    $router->get('/admin/reports', [$reports, 'show'])->admin();
};
