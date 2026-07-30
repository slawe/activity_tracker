<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Security;

final class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(
        private readonly Session $session,
    ) {
    }

    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public function isValid(?string $token): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);

        return is_string($token) && is_string($expected) && hash_equals($expected, $token);
    }

    public function refresh(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }
}
