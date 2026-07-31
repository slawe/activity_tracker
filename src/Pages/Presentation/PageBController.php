<?php

declare(strict_types=1);

namespace App\Pages\Presentation;

use App\Activity\Domain\ActivityPage;
use App\Auth\Application\CurrentUserProvider;
use App\Pages\Application\DownloadFileHandler;
use App\Pages\Application\ViewPageHandler;
use App\Shared\Kernel\Http\FileDownloadResponse;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;

final class PageBController
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
        private readonly ViewPageHandler $viewPage,
        private readonly DownloadFileHandler $downloadFile,
        private readonly CsrfTokenManager $csrf,
        private readonly ViewRenderer $views,
    ) {
    }

    public function show(Request $request): Response
    {
        $user = $this->currentUserProvider->requireUser();

        $this->viewPage->handle($user->id, ActivityPage::B, $request->ipAddress(), $request->userAgent());

        return new HtmlResponse($this->views->render('pages/page-b.php', [
            'title' => 'Page B',
            'currentUser' => $user,
            'csrfToken' => $this->csrf->token(),
        ]));
    }

    public function download(Request $request): Response
    {
        $user = $this->currentUserProvider->requireUser();

        $path = $this->downloadFile->handle($user->id, $request->ipAddress(), $request->userAgent());

        return new FileDownloadResponse($path, 'sample.exe');
    }
}
