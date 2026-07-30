<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Http;

use App\Shared\Kernel\Response;

final class RedirectResponse extends Response
{
    public function __construct(string $location, int $statusCode = 302)
    {
        parent::__construct($statusCode, ['Location' => $location]);
    }

    protected function sendBody(): void
    {
    }
}
