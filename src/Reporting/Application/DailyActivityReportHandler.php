<?php

declare(strict_types=1);

namespace App\Reporting\Application;

use App\Reporting\Domain\DailyActivityReport;
use App\Reporting\Domain\DailyActivityReportRepositoryInterface;
use DateInterval;
use DateTimeImmutable;

final readonly class DailyActivityReportHandler
{
    public function __construct(
        private DailyActivityReportRepositoryInterface $reports,
    ) {
    }

    /**
     * @return list<DailyActivityReport>
     */
    public function handle(DailyActivityReportQuery $query): array
    {
        [$dateFrom, $dateTo] = $this->normalizeRange($query);

        return $this->fillMissingDates(
            $dateFrom,
            $dateTo,
            $this->reports->findBetween($dateFrom, $dateTo),
        );
    }

    /**
     * @return array{DateTimeImmutable, DateTimeImmutable}
     */
    private function normalizeRange(DailyActivityReportQuery $query): array
    {
        $dateFrom = $query->dateFrom->setTime(0, 0);
        $dateTo = $query->dateTo->setTime(0, 0);

        return $dateFrom <= $dateTo
            ? [$dateFrom, $dateTo]
            : [$dateTo, $dateFrom];
    }

    /**
     * @param list<DailyActivityReport> $existingReports
     * @return list<DailyActivityReport>
     */
    private function fillMissingDates(
        DateTimeImmutable $dateFrom,
        DateTimeImmutable $dateTo,
        array $existingReports,
    ): array {
        $reportsByDate = $this->indexByDate($existingReports);
        $oneDay = new DateInterval('P1D');

        $reports = [];
        for ($date = $dateFrom; $date <= $dateTo; $date = $date->add($oneDay)) {
            $dateKey = $date->format('Y-m-d');
            $reports[] = $reportsByDate[$dateKey] ?? $this->emptyReport($date);
        }

        return $reports;
    }

    /**
     * @param list<DailyActivityReport> $reports
     * @return array<string, DailyActivityReport>
     */
    private function indexByDate(array $reports): array
    {
        $reportsByDate = [];
        foreach ($reports as $report) {
            $reportsByDate[$report->date->format('Y-m-d')] = $report;
        }

        return $reportsByDate;
    }

    private function emptyReport(DateTimeImmutable $date): DailyActivityReport
    {
        return new DailyActivityReport($date, 0, 0, 0, 0);
    }
}
