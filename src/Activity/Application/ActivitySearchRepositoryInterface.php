<?php

declare(strict_types=1);

namespace App\Activity\Application;

interface ActivitySearchRepositoryInterface
{
    public function search(ActivitySearchQuery $query): ActivitySearchResult;
}
