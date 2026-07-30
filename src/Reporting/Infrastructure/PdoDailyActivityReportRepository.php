<?php

declare(strict_types=1);

namespace App\Reporting\Infrastructure;

use App\Reporting\Domain\DailyActivityReport;
use App\Reporting\Domain\DailyActivityReportRepositoryInterface;
use DateTimeImmutable;
use PDO;

final class PdoDailyActivityReportRepository implements DailyActivityReportRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function increment(
        DateTimeImmutable $date,
        int $pageAViews,
        int $pageBViews,
        int $buyCowClicks,
        int $downloadClicks,
    ): void {
        $statement = $this->connection->prepare(
            'INSERT INTO daily_activity_reports
                (report_date, page_a_views, page_b_views, buy_cow_clicks, download_clicks, updated_at)
             VALUES
                (:report_date, :page_a_views, :page_b_views, :buy_cow_clicks, :download_clicks, :updated_at)
             ON DUPLICATE KEY UPDATE
                page_a_views = page_a_views + VALUES(page_a_views),
                page_b_views = page_b_views + VALUES(page_b_views),
                buy_cow_clicks = buy_cow_clicks + VALUES(buy_cow_clicks),
                download_clicks = download_clicks + VALUES(download_clicks),
                updated_at = VALUES(updated_at)',
        );
        $statement->execute([
            'report_date' => $date->format('Y-m-d'),
            'page_a_views' => $pageAViews,
            'page_b_views' => $pageBViews,
            'buy_cow_clicks' => $buyCowClicks,
            'download_clicks' => $downloadClicks,
            'updated_at' => $date->format('Y-m-d H:i:s'),
        ]);
    }

    public function findBetween(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array
    {
        // Long-range reports read pre-aggregated rows, never the raw event table.
        $statement = $this->connection->prepare(
            'SELECT report_date, page_a_views, page_b_views, buy_cow_clicks, download_clicks
             FROM daily_activity_reports
             WHERE report_date BETWEEN :date_from AND :date_to
             ORDER BY report_date ASC',
        );
        $statement->execute([
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
        ]);

        $reports = [];
        while ($row = $statement->fetch()) {
            $reports[] = new DailyActivityReport(
                new DateTimeImmutable((string) $row['report_date']),
                (int) $row['page_a_views'],
                (int) $row['page_b_views'],
                (int) $row['buy_cow_clicks'],
                (int) $row['download_clicks'],
            );
        }

        return $reports;
    }
}
