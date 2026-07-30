<?php

declare(strict_types=1);

namespace App\Reporting\Application;

use App\Reporting\Domain\DailyActivityReport;
use App\Reporting\Domain\DailyActivityReportRepositoryInterface;

final class DailyActivityReportHandler
{
    public function __construct(
        private readonly DailyActivityReportRepositoryInterface $reports,
    ) {
    }

    /**
     * @return list<DailyActivityReport>
     */
    public function handle(DailyActivityReportQuery $query): array
    {
        return $this->reports->findBetween($query->dateFrom, $query->dateTo);
    }
}
