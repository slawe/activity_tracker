<?php

declare(strict_types=1);

namespace App\Reporting\Presentation;

use App\Auth\Presentation\AdminAccessGuard;
use App\Reporting\Application\DailyActivityReportHandler;
use App\Reporting\Application\DailyActivityReportQuery;
use App\Shared\Kernel\Http\HtmlResponse;
use App\Shared\Kernel\Request;
use App\Shared\Kernel\Response;
use App\Shared\Kernel\Security\CsrfTokenManager;
use App\Shared\Kernel\View\ViewRenderer;
use DateTimeImmutable;

final class ReportsController
{
    public function __construct(
        private readonly AdminAccessGuard $adminAccessGuard,
        private readonly DailyActivityReportHandler $reports,
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

        $today = new DateTimeImmutable('today');
        $dateFrom = $this->date($request->queryString('date_from')) ?? $today->modify('-29 days');
        $dateTo = $this->date($request->queryString('date_to')) ?? $today;
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $reports = $this->reports->handle(new DailyActivityReportQuery($dateFrom, $dateTo));
        $chartData = array_map(static fn ($report): array => [
            'date' => $report->date->format('Y-m-d'),
            'pageAViews' => $report->pageAViews,
            'pageBViews' => $report->pageBViews,
            'buyCowClicks' => $report->buyCowClicks,
            'downloadClicks' => $report->downloadClicks,
        ], $reports);

        return new HtmlResponse($this->views->render('admin/reports.php', [
            'title' => 'Daily Reports',
            'currentUser' => $admin,
            'csrfToken' => $this->csrf->token(),
            'reports' => $reports,
            'chartData' => $chartData,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
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
