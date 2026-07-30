<?php

declare(strict_types=1);

namespace App\Reporting\Domain;

use DateTimeImmutable;

final readonly class DailyActivityReport
{
    public function __construct(
        public DateTimeImmutable $date,
        public int $pageAViews,
        public int $pageBViews,
        public int $buyCowClicks,
        public int $downloadClicks,
    ) {
    }
}
