<?php

declare(strict_types=1);

namespace App\Pages\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityPage;
use App\Activity\Domain\ActivityTarget;
use App\Pages\Domain\UserPageStateRepositoryInterface;
use App\Shared\Kernel\Database\TransactionManager;
use DateTimeImmutable;

final class BuyCowHandler
{
    public function __construct(
        private readonly UserPageStateRepositoryInterface $states,
        private readonly ActivityTracker $activityTracker,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function hasBoughtCow(int $userId): bool
    {
        return $this->states->exists($userId, ActivityPage::A->value, ActivityTarget::BuyCow->value);
    }

    public function handle(int $userId, string $ipAddress, string $userAgent): void
    {
        $this->transactions->run(function () use ($userId, $ipAddress, $userAgent): void {
            $created = $this->states->addIfMissing(
                $userId,
                ActivityPage::A->value,
                ActivityTarget::BuyCow->value,
                new DateTimeImmutable(),
            );

            if ($created) {
                $this->activityTracker->track(new TrackActivityCommand(
                    $userId,
                    ActivityAction::ButtonClick,
                    ActivityPage::A,
                    ActivityTarget::BuyCow,
                    $ipAddress,
                    $userAgent,
                ));
            }
        });
    }
}
