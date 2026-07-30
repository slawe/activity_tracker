<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Activity\Domain\ActivityAction;
use DateTimeImmutable;

final readonly class ActivitySearchQuery
{
    public const int PAGE_SIZE = 50;

    public function __construct(
        public ?DateTimeImmutable $dateFrom,
        public ?DateTimeImmutable $dateTo,
        public ?int $userId,
        public ?ActivityAction $action,
        public int $page,
    ) {
    }
}
