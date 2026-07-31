<?php

declare(strict_types=1);

namespace App\Pages\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityPage;

final class ViewPageHandler
{
    public function __construct(
        private readonly ActivityTracker $activityTracker,
    ) {
    }

    public function handle(int $userId, ActivityPage $page, string $ipAddress, string $userAgent): void
    {
        $this->activityTracker->track(new TrackActivityCommand(
            $userId,
            ActivityAction::ViewPage,
            $page,
            null,
            $ipAddress,
            $userAgent,
        ));
    }
}
