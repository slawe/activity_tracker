<?php

declare(strict_types=1);

namespace App\Reporting\Presentation;

use App\Activity\Application\ActivitySearchHandler;
use App\Activity\Application\ActivitySearchQuery;
use App\Activity\Application\ActivitySearchResult;
use App\Activity\Domain\ActivityAction;
use App\Auth\Domain\UserRepositoryInterface;
use App\Auth\Presentation\AdminAccessGuard;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;
use DateTimeImmutable;

final class StatsController
{
    public function __construct(
        private readonly AdminAccessGuard $adminAccessGuard,
        private readonly ActivitySearchHandler $search,
        private readonly UserRepositoryInterface $users,
        private readonly CsrfTokenManager $csrf,
        private readonly ViewRenderer $views,
    ) {
    }

    public function show(Request $request): Response
    {
        $admin = $this->adminAccessGuard->requireAdmin();

        if ($admin instanceof Response) {
            return $admin;
        }

        $dateFrom = $this->date($request->queryString('date_from'));
        $dateTo = $this->date($request->queryString('date_to'))?->modify('+1 day');
        $userEmail = strtolower($request->queryString('user_email', '') ?? '');
        // Resolve through the unique email index instead of loading every user for a filter.
        $filteredUser = $userEmail === '' ? null : $this->users->findByEmail($userEmail);
        $actionValue = $request->queryString('action');
        $action = $actionValue === null || $actionValue === ''
            ? null
            : ActivityAction::tryFrom($actionValue);
        $page = max(1, (int) ($request->queryString('page', '1') ?? '1'));

        $result = $userEmail !== '' && $filteredUser === null
            ? new ActivitySearchResult([], 0, 1, ActivitySearchQuery::PAGE_SIZE)
            : $this->search->handle(new ActivitySearchQuery(
                $dateFrom,
                $dateTo,
                $filteredUser?->id,
                $action,
                $page,
            ));

        return new HtmlResponse($this->views->render('admin/stats.php', [
            'title' => 'Activity Statistics',
            'currentUser' => $admin,
            'csrfToken' => $this->csrf->token(),
            'result' => $result,
            'actions' => ActivityAction::cases(),
            'filters' => [
                'date_from' => $request->queryString('date_from', '') ?? '',
                'date_to' => $request->queryString('date_to', '') ?? '',
                'user_email' => $request->queryString('user_email', '') ?? '',
                'action' => $actionValue ?? '',
            ],
        ]));
    }

    private function date(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }
}
