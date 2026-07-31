<?php

declare(strict_types=1);

namespace App\Shared\Kernel;

use App\Activity\Application\ActivitySearchHandler;
use App\Activity\Application\ActivitySearchRepositoryInterface;
use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityHandler;
use App\Activity\Domain\ActivityRepositoryInterface;
use App\Activity\Infrastructure\PdoActivityRepository;
use App\Auth\Application\CurrentUserProvider;
use App\Auth\Application\LoginUserHandler;
use App\Auth\Application\LogoutUserHandler;
use App\Auth\Application\RegisterUserHandler;
use App\Auth\Domain\UserRepositoryInterface;
use App\Auth\Infrastructure\PdoUserRepository;
use App\Auth\Presentation\Guards\AdminGuard;
use App\Auth\Presentation\Guards\AuthGuard;
use App\Auth\Presentation\Guards\CsrfGuard;
use App\Auth\Presentation\LoginController;
use App\Auth\Presentation\LogoutController;
use App\Auth\Presentation\RegisterController;
use App\Pages\Application\BuyCowHandler;
use App\Pages\Application\DownloadFileHandler;
use App\Pages\Application\ViewPageHandler;
use App\Pages\Domain\UserPageStateRepositoryInterface;
use App\Pages\Infrastructure\PdoUserPageStateRepository;
use App\Pages\Presentation\PageAController;
use App\Pages\Presentation\PageBController;
use App\Reporting\Application\DailyActivityCounter;
use App\Reporting\Application\DailyActivityReportHandler;
use App\Reporting\Domain\DailyActivityReportRepositoryInterface;
use App\Reporting\Infrastructure\PdoDailyActivityReportRepository;
use App\Reporting\Presentation\ReportsController;
use App\Reporting\Presentation\StatsController;
use App\Shared\Kernel\Database\Connection;
use App\Shared\Kernel\Database\TransactionManager;
use App\Shared\Kernel\Routing\RouteGuardRunner;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\Security\PasswordHasher;
use App\Shared\Kernel\Security\Session;
use App\Shared\Kernel\View\ViewRenderer;
use PDO;

final class Application
{
    private function __construct(
        private readonly Router $router,
    ) {
    }

    public static function boot(string $projectRoot): self
    {
        $container = new Container();
        self::registerServices($container, $projectRoot);

        $session = $container->get(Session::class);
        assert($session instanceof Session);
        $session->start(getenv('SESSION_NAME') ?: 'activity_tracker_session');

        $guardRunner = $container->get(RouteGuardRunner::class);
        assert($guardRunner instanceof RouteGuardRunner);
        $router = new Router($guardRunner);
        $routes = require $projectRoot . '/config/routes.php';
        $routes($router, $container);

        return new self($router);
    }

    public function run(): void
    {
        $response = $this->router->dispatch(Request::fromGlobals());
        $response->send();
    }

    private static function registerServices(Container $container, string $projectRoot): void
    {
        self::registerSharedServices($container, $projectRoot);
        self::registerRepositories($container);
        self::registerReportingServices($container);
        self::registerActivityServices($container);
        self::registerAuthServices($container);
        self::registerPageServices($container, $projectRoot);
        self::registerControllers($container);
    }

    private static function registerSharedServices(Container $container, string $projectRoot): void
    {
        $container->set(PDO::class, static function () use ($projectRoot): PDO {
            /** @var array{host: string, port: int, database: string, username: string, password: string} $config */
            $config = require $projectRoot . '/config/database.php';

            return Connection::create($config);
        });
        $container->set(Session::class, static fn (): Session => new Session());
        $container->set(PasswordHasher::class, static fn (): PasswordHasher => new PasswordHasher());
        $container->set(CsrfTokenManager::class, static fn (Container $c): CsrfTokenManager => new CsrfTokenManager(
            $c->get(Session::class),
        ));
        $container->set(ViewRenderer::class, static fn (): ViewRenderer => new ViewRenderer($projectRoot . '/views'));
        $container->set(TransactionManager::class, static fn (Container $c): TransactionManager => new TransactionManager(
            $c->get(PDO::class),
        ));
        $container->set(RouteGuardRunner::class, static fn (Container $c): RouteGuardRunner => new RouteGuardRunner($c));
    }

    private static function registerRepositories(Container $container): void
    {
        $container->set(
            UserRepositoryInterface::class,
            static fn (Container $c): UserRepositoryInterface => new PdoUserRepository($c->get(PDO::class)),
        );
        $container->set(PdoActivityRepository::class, static fn (Container $c): PdoActivityRepository => new PdoActivityRepository(
            $c->get(PDO::class),
        ));
        $container->set(ActivityRepositoryInterface::class, static fn (Container $c): ActivityRepositoryInterface => $c->get(
            PdoActivityRepository::class,
        ));
        $container->set(ActivitySearchRepositoryInterface::class, static fn (Container $c): ActivitySearchRepositoryInterface => $c->get(
            PdoActivityRepository::class,
        ));
        $container->set(
            UserPageStateRepositoryInterface::class,
            static fn (Container $c): UserPageStateRepositoryInterface => new PdoUserPageStateRepository(
                $c->get(PDO::class),
            ),
        );
        $container->set(
            DailyActivityReportRepositoryInterface::class,
            static fn (Container $c): DailyActivityReportRepositoryInterface => new PdoDailyActivityReportRepository(
                $c->get(PDO::class),
            ),
        );
    }

    private static function registerActivityServices(Container $container): void
    {
        $container->set(TrackActivityHandler::class, static fn (Container $c): TrackActivityHandler => new TrackActivityHandler($c->get(ActivityRepositoryInterface::class)));
        $container->set(ActivityTracker::class, static fn (Container $c): ActivityTracker => new ActivityTracker(
            $c->get(TrackActivityHandler::class),
            $c->get(DailyActivityCounter::class),
            $c->get(TransactionManager::class),
        ));
        $container->set(ActivitySearchHandler::class, static fn (Container $c): ActivitySearchHandler => new ActivitySearchHandler($c->get(ActivitySearchRepositoryInterface::class)));
    }

    private static function registerAuthServices(Container $container): void
    {
        $container->set(CurrentUserProvider::class, static fn (Container $c): CurrentUserProvider => new CurrentUserProvider(
            $c->get(Session::class),
            $c->get(UserRepositoryInterface::class),
        ));
        $container->set(AuthGuard::class, static fn (Container $c): AuthGuard => new AuthGuard(
            $c->get(CurrentUserProvider::class),
        ));
        $container->set(AdminGuard::class, static fn (Container $c): AdminGuard => new AdminGuard(
            $c->get(CurrentUserProvider::class),
        ));
        $container->set(CsrfGuard::class, static fn (Container $c): CsrfGuard => new CsrfGuard(
            $c->get(CsrfTokenManager::class),
        ));
        $container->set(RegisterUserHandler::class, static fn (Container $c): RegisterUserHandler => new RegisterUserHandler(
            $c->get(UserRepositoryInterface::class),
            $c->get(PasswordHasher::class),
            $c->get(ActivityTracker::class),
            $c->get(TransactionManager::class),
        ));
        $container->set(LoginUserHandler::class, static fn (Container $c): LoginUserHandler => new LoginUserHandler(
            $c->get(UserRepositoryInterface::class),
            $c->get(PasswordHasher::class),
            $c->get(Session::class),
            $c->get(ActivityTracker::class),
        ));
        $container->set(LogoutUserHandler::class, static fn (Container $c): LogoutUserHandler => new LogoutUserHandler(
            $c->get(Session::class),
            $c->get(CurrentUserProvider::class),
            $c->get(ActivityTracker::class),
        ));
    }

    private static function registerPageServices(Container $container, string $projectRoot): void
    {
        $container->set(ViewPageHandler::class, static fn (Container $c): ViewPageHandler => new ViewPageHandler($c->get(ActivityTracker::class)));
        $container->set(BuyCowHandler::class, static fn (Container $c): BuyCowHandler => new BuyCowHandler(
            $c->get(UserPageStateRepositoryInterface::class),
            $c->get(ActivityTracker::class),
            $c->get(TransactionManager::class),
        ));
        $container->set(DownloadFileHandler::class, static fn (Container $c): DownloadFileHandler => new DownloadFileHandler(
            $c->get(ActivityTracker::class),
            // Request data must never select a filesystem path.
            $projectRoot . '/public/download/sample.exe',
        ));
    }

    private static function registerReportingServices(Container $container): void
    {
        $container->set(DailyActivityCounter::class, static fn (Container $c): DailyActivityCounter => new DailyActivityCounter(
            $c->get(DailyActivityReportRepositoryInterface::class),
        ));
        $container->set(DailyActivityReportHandler::class, static fn (Container $c): DailyActivityReportHandler => new DailyActivityReportHandler(
            $c->get(DailyActivityReportRepositoryInterface::class),
        ));
    }

    private static function registerControllers(Container $container): void
    {
        $container->set(LoginController::class, static fn (Container $c): LoginController => new LoginController(
            $c->get(LoginUserHandler::class),
            $c->get(CurrentUserProvider::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
        $container->set(RegisterController::class, static fn (Container $c): RegisterController => new RegisterController(
            $c->get(RegisterUserHandler::class),
            $c->get(CurrentUserProvider::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
        $container->set(LogoutController::class, static fn (Container $c): LogoutController => new LogoutController(
            $c->get(LogoutUserHandler::class),
            $c->get(CsrfTokenManager::class),
        ));
        $container->set(PageAController::class, static fn (Container $c): PageAController => new PageAController(
            $c->get(CurrentUserProvider::class),
            $c->get(ViewPageHandler::class),
            $c->get(BuyCowHandler::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
        $container->set(PageBController::class, static fn (Container $c): PageBController => new PageBController(
            $c->get(CurrentUserProvider::class),
            $c->get(ViewPageHandler::class),
            $c->get(DownloadFileHandler::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
        $container->set(StatsController::class, static fn (Container $c): StatsController => new StatsController(
            $c->get(CurrentUserProvider::class),
            $c->get(ActivitySearchHandler::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
        $container->set(ReportsController::class, static fn (Container $c): ReportsController => new ReportsController(
            $c->get(CurrentUserProvider::class),
            $c->get(DailyActivityReportHandler::class),
            $c->get(CsrfTokenManager::class),
            $c->get(ViewRenderer::class),
        ));
    }
}
