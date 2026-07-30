<?php

declare(strict_types=1);

namespace App\Auth\Domain;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
}
