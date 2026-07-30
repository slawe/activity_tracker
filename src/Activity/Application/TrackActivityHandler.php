<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Activity\Domain\ActivityEvent;
use App\Activity\Domain\ActivityRepositoryInterface;
use DateTimeImmutable;

final class TrackActivityHandler
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activities,
    ) {
    }

    public function handle(TrackActivityCommand $command): ActivityEvent
    {
        $event = new ActivityEvent(
            $command->userId,
            $command->action,
            $command->page,
            $command->target,
            $command->ipAddress,
            $command->userAgent,
            $command->occurredAt ?? new DateTimeImmutable(),
        );
        $this->activities->add($event);

        return $event;
    }
}
