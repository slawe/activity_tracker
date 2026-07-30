<?php

declare(strict_types=1);

namespace App\Reporting\Domain;

use DateTimeImmutable;

interface DailyActivityReportRepositoryInterface
{
    public function increment(
        DateTimeImmutable $date,
        int $pageAViews,
        int $pageBViews,
        int $buyCowClicks,
        int $downloadClicks,
    ): void;

    /**
     * @return list<DailyActivityReport>
     */
    public function findBetween(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array;
}
