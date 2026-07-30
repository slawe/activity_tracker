<?php

declare(strict_types=1);

namespace App\Reporting\Application;

use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityEvent;
use App\Reporting\Domain\DailyActivityReportRepositoryInterface;

final class DailyActivityCounter
{
    public function __construct(
        private readonly DailyActivityReportRepositoryInterface $reports,
    ) {
    }

    public function count(ActivityEvent $event): void
    {
        $increments = match (true) {
            $event->action === ActivityAction::ViewPage && $event->page === 'A' => [1, 0, 0, 0],
            $event->action === ActivityAction::ViewPage && $event->page === 'B' => [0, 1, 0, 0],
            $event->action === ActivityAction::ButtonClick
                && $event->page === 'A'
                && $event->target === 'buy-a-cow' => [0, 0, 1, 0],
            $event->action === ActivityAction::ButtonClick
                && $event->page === 'B'
                && $event->target === 'download' => [0, 0, 0, 1],
            default => null,
        };

        if ($increments === null) {
            return;
        }

        $this->reports->increment($event->createdAt, ...$increments);
    }
}
