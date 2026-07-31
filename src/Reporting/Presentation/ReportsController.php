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

        $dateRange = DateFilterRange::fromInput(
            $request->queryString('date_from'),
            $request->queryString('date_to'),
        );

        $reports = $this->reports->handle(new DailyActivityReportQuery(
            $dateRange->dateFrom,
            $dateRange->dateTo,
        ));
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
            'dateFrom' => $dateRange->dateFrom->format('Y-m-d'),
            'dateTo' => $dateRange->dateTo->format('Y-m-d'),
            'today' => $dateRange->today->format('Y-m-d'),
        ]));
    }
}
