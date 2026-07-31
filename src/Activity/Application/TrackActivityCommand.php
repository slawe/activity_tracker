<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityPage;
use App\Activity\Domain\ActivityTarget;
use DateTimeImmutable;

final readonly class TrackActivityCommand
{
    public function __construct(
        public ?int $userId,
        public ActivityAction $action,
        public ?ActivityPage $page,
        public ?ActivityTarget $target,
        public string $ipAddress,
        public string $userAgent,
        public ?DateTimeImmutable $occurredAt = null,
    ) {
    }
}
