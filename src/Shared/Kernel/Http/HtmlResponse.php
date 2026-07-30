<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Http;

use App\Shared\Kernel\Response;

final class HtmlResponse extends Response
{
    public function __construct(
        private readonly string $content,
        int $statusCode = 200,
    ) {
        parent::__construct($statusCode, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    protected function sendBody(): void
    {
        echo $this->content;
    }
}
