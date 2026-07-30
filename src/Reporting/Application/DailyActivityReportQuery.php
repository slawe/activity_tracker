<?php

declare(strict_types=1);

namespace App\Reporting\Application;

use DateTimeImmutable;

final readonly class DailyActivityReportQuery
{
    public function __construct(
        public DateTimeImmutable $dateFrom,
        public DateTimeImmutable $dateTo,
    ) {
    }
}
