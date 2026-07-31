<?php

declare(strict_types=1);

namespace App\Activity\Application;

final class ActivitySearchHandler
{
    public function __construct(
        private readonly ActivitySearchRepositoryInterface $activities,
    ) {
    }

    public function handle(ActivitySearchQuery $query): ActivitySearchResult
    {
        return $this->activities->search($query);
    }
}
