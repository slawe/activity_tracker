<?php

declare(strict_types=1);

namespace App\Pages\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityPage;
use App\Activity\Domain\ActivityTarget;

final class DownloadFileHandler
{
    public function __construct(
        private readonly ActivityTracker $activityTracker,
        private readonly string $downloadPath,
    ) {
    }

    public function handle(int $userId, string $ipAddress, string $userAgent): string
    {
        $this->activityTracker->track(new TrackActivityCommand(
            $userId,
            ActivityAction::ButtonClick,
            ActivityPage::B,
            ActivityTarget::Download,
            $ipAddress,
            $userAgent,
        ));

        return $this->downloadPath;
    }
}
