<?php

declare(strict_types=1);

namespace App\Activity\Domain;

interface ActivityRepositoryInterface
{
    public function add(ActivityEvent $event): void;
}
