<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Shared\Kernel\Security\Session;

final class LogoutUserHandler
{
    public function __construct(
        private readonly Session $session,
        private readonly CurrentUserProvider $currentUserProvider,
        private readonly ActivityTracker $activityTracker,
    ) {
    }

    public function handle(string $ipAddress, string $userAgent): void
    {
        $currentUser = $this->currentUserProvider->get();
        try {
            if ($currentUser !== null) {
                $this->activityTracker->track(new TrackActivityCommand(
                    $currentUser->id,
                    ActivityAction::Logout,
                    null,
                    null,
                    $ipAddress,
                    $userAgent,
                ));
            }
        } finally {
            $this->session->remove('authenticated_user');
            $this->session->regenerate();
        }
    }
}
