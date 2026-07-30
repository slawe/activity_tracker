<?php

declare(strict_types=1);

namespace App\Activity\Application;

use App\Activity\Domain\ActivityRepositoryInterface;

final class ActivitySearchHandler
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activities,
    ) {
    }

    public function handle(ActivitySearchQuery $query): ActivitySearchResult
    {
        return $this->activities->search($query);
    }
}
