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
    $router->post('/login', [$login, 'submit']);
    $router->get('/register', [$register, 'show']);
    $router->post('/register', [$register, 'submit']);
    $router->post('/logout', [$logout, 'submit']);
    $router->get('/page-a', [$pageA, 'show']);
    $router->post('/page-a/buy-cow', [$pageA, 'buy']);
    $router->get('/page-b', [$pageB, 'show']);
    $router->post('/page-b/download', [$pageB, 'download']);
    $router->get('/admin/stats', [$stats, 'show']);
    $router->get('/admin/reports', [$reports, 'show']);
};
