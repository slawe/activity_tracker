<?php

declare(strict_types=1);

namespace App\Activity\Domain;

use App\Activity\Application\ActivitySearchQuery;
use App\Activity\Application\ActivitySearchResult;

interface ActivityRepositoryInterface
{
    public function add(ActivityEvent $event): void;

    public function search(ActivitySearchQuery $query): ActivitySearchResult;
}
