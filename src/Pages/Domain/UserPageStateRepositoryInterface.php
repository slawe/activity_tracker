<?php

declare(strict_types=1);

namespace App\Pages\Domain;

use DateTimeImmutable;

interface UserPageStateRepositoryInterface
{
    public function exists(int $userId, string $page, string $action): bool;

    public function addIfMissing(int $userId, string $page, string $action, DateTimeImmutable $createdAt): bool;
}
