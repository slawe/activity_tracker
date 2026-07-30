<?php

declare(strict_types=1);

namespace App\Pages\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Pages\Domain\UserPageStateRepositoryInterface;
use App\Shared\Kernel\Database\TransactionManager;
use DateTimeImmutable;

final class BuyCowHandler
{
    private const PAGE = 'A';
    private const ACTION = 'buy-a-cow';

    public function __construct(
        private readonly UserPageStateRepositoryInterface $states,
        private readonly ActivityTracker $activityTracker,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function hasBoughtCow(int $userId): bool
    {
        return $this->states->exists($userId, self::PAGE, self::ACTION);
    }

    public function handle(int $userId, string $ipAddress, string $userAgent): void
    {
        $this->transactions->run(function () use ($userId, $ipAddress, $userAgent): void {
            $created = $this->states->addIfMissing(
                $userId,
                self::PAGE,
                self::ACTION,
                new DateTimeImmutable(),
            );

            if ($created) {
                $this->activityTracker->track(new TrackActivityCommand(
                    $userId,
                    ActivityAction::ButtonClick,
                    self::PAGE,
                    self::ACTION,
                    $ipAddress,
                    $userAgent,
                ));
            }
        });
    }
}
