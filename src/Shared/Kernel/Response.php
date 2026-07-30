<?php

declare(strict_types=1);

namespace App\Shared\Kernel;

abstract class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $statusCode = 200,
        private readonly array $headers = [],
    ) {
    }

    final public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        $this->sendBody();
    }

    abstract protected function sendBody(): void;
}
