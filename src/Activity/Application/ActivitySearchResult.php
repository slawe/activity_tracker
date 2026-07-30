<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Activity\Domain\ActivityEvent;

final readonly class ActivitySearchResult
{
    /**
     * @param list<ActivityEvent> $events
     */
    public function __construct(
        public array $events,
        public int $total,
        public int $page,
        public int $pageSize,
    ) {
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total / $this->pageSize));
    }
}
