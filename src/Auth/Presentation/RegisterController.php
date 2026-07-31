<?php

declare(strict_types=1);

namespace App\Auth\Presentation;

use App\Auth\Application\CurrentUserProvider;
use App\Auth\Application\RegisterUserCommand;
use App\Auth\Application\RegisterUserHandler;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Http\RedirectResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;
use DomainException;

final class RegisterController
{
    public function __construct(
        private readonly RegisterUserHandler $handler,
        private readonly CurrentUserProvider $currentUserProvider,
        private readonly CsrfTokenManager $csrf,
        private readonly ViewRenderer $views,
    ) {
    }

    public function show(Request $request): Response
    {
        if ($this->currentUserProvider->get() !== null) {
            return new RedirectResponse('/page-a');
        }

        return $this->render();
    }

    public function submit(Request $request): Response
    {
        if (!$this->csrf->isValid($request->postString('_csrf'))) {
            return $this->render('The form expired. Please try again.', 419);
        }

        try {
            $this->handler->handle(new RegisterUserCommand(
                $request->postString('email', '') ?? '',
                $request->postRawString('password', '') ?? '',
                $request->ipAddress(),
                $request->userAgent(),
            ));
        } catch (DomainException $exception) {
            return $this->render($exception->getMessage(), 422);
        }

        $this->csrf->refresh();

        return new RedirectResponse('/login?registered=1');
    }

    private function render(?string $error = null, int $statusCode = 200): HtmlResponse
    {
        return new HtmlResponse($this->views->render('auth/register.php', [
            'title' => 'Register',
            'currentUser' => null,
            'csrfToken' => $this->csrf->token(),
            'error' => $error,
        ]), $statusCode);
    }
}
