<?php

declare(strict_types=1);

namespace App\Activity\Domain;

enum ActivityTarget: string
{
    case BuyCow = 'buy-a-cow';
    case Download = 'download';
}
