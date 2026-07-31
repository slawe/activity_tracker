<?php

declare(strict_types=1);

namespace App\Reporting\Application;

use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityEvent;
use App\Activity\Domain\ActivityPage;
use App\Activity\Domain\ActivityTarget;
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
            $event->action === ActivityAction::ViewPage && $event->page === ActivityPage::A => [1, 0, 0, 0],
            $event->action === ActivityAction::ViewPage && $event->page === ActivityPage::B => [0, 1, 0, 0],
            $event->action === ActivityAction::ButtonClick
                && $event->page === ActivityPage::A
                && $event->target === ActivityTarget::BuyCow => [0, 0, 1, 0],
            $event->action === ActivityAction::ButtonClick
                && $event->page === ActivityPage::B
                && $event->target === ActivityTarget::Download => [0, 0, 0, 1],
            default => null,
        };

        if ($increments === null) {
            return;
        }

        $this->reports->increment($event->createdAt, ...$increments);
    }
}
