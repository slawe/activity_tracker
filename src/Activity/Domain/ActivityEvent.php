<?php

declare(strict_types=1);

namespace App\Activity\Domain;

use DateTimeImmutable;

final readonly class ActivityEvent
{
    public function __construct(
        public ?int $userId,
        public ActivityAction $action,
        public ?ActivityPage $page,
        public ?ActivityTarget $target,
        public string $ipAddress,
        public string $userAgent,
        public DateTimeImmutable $createdAt,
        public ?string $userEmail = null,
    ) {
    }
}
