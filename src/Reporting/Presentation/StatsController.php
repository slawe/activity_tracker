<?php

declare(strict_types=1);

namespace App\Reporting\Presentation;

use App\Activity\Application\ActivitySearchHandler;
use App\Activity\Application\ActivitySearchQuery;
use App\Activity\Application\ActivitySearchResult;
use App\Activity\Domain\ActivityAction;
use App\Auth\Application\CurrentUserProvider;
use App\Auth\Domain\UserRepositoryInterface;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;

final class StatsController
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
        private readonly ActivitySearchHandler $search,
        private readonly UserRepositoryInterface $users,
        private readonly CsrfTokenManager $csrf,
        private readonly ViewRenderer $views,
    ) {
    }

    public function show(Request $request): Response
    {
        $admin = $this->currentUserProvider->requireUser();

        $dateRange = DateFilterRange::fromInput(
            $request->queryString('date_from'),
            $request->queryString('date_to'),
        );
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
                $dateRange->dateFrom,
                $dateRange->dateTo->modify('+1 day'),
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
            'today' => $dateRange->today->format('Y-m-d'),
            'filters' => [
                'date_from' => $dateRange->dateFrom->format('Y-m-d'),
                'date_to' => $dateRange->dateTo->format('Y-m-d'),
                'user_email' => $request->queryString('user_email', '') ?? '',
                'action' => $actionValue ?? '',
            ],
        ]));
    }

}
