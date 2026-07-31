<?php

declare(strict_types=1);

namespace App\Pages\Presentation;

use App\Activity\Domain\ActivityPage;
use App\Auth\Application\CurrentUserProvider;
use App\Pages\Application\BuyCowHandler;
use App\Pages\Application\ViewPageHandler;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;

final class PageAController
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
        private readonly ViewPageHandler $viewPage,
        private readonly BuyCowHandler $buyCow,
        private readonly CsrfTokenManager $csrf,
        private readonly ViewRenderer $views,
    ) {
    }

    public function show(Request $request): Response
    {
        $user = $this->currentUserProvider->requireUser();

        $this->viewPage->handle($user->id, ActivityPage::A, $request->ipAddress(), $request->userAgent());

        return new HtmlResponse($this->views->render('pages/page-a.php', [
            'title' => 'Page A',
            'currentUser' => $user,
            'csrfToken' => $this->csrf->token(),
            'hasBoughtCow' => $this->buyCow->hasBoughtCow($user->id),
        ]));
    }

    public function buy(Request $request): Response
    {
        $user = $this->currentUserProvider->requireUser();

        $this->buyCow->handle($user->id, $request->ipAddress(), $request->userAgent());

        return new RedirectResponse('/page-a');
    }
}
