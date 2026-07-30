<?php

declare(strict_types=1);

namespace App\Auth\Domain;

use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('An account with this email already exists.');
    }
}
