<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Reporting\Application\DailyActivityCounter;
use App\Shared\Kernel\Database\TransactionManager;

final class ActivityTracker
{
    public function __construct(
        private readonly TrackActivityHandler $trackActivity,
        private readonly DailyActivityCounter $dailyCounter,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function track(TrackActivityCommand $command): void
    {
        // Keep the raw audit event and its daily aggregate consistent.
        $this->transactions->run(function () use ($command): void {
            $event = $this->trackActivity->handle($command);
            $this->dailyCounter->count($event);
        });
    }
}
