<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Domain\UserRepositoryInterface;
use App\Shared\Kernel\Security\AuthenticatedUser;
use App\Shared\Kernel\Security\Session;

final class CurrentUserProvider
{
    public function __construct(
        private readonly Session $session,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function get(): ?AuthenticatedUser
    {
        $data = $this->session->get('authenticated_user');
        if (!is_array($data) || !is_int($data['id'] ?? null)) {
            return null;
        }

        $user = $this->users->findById($data['id']);
        if ($user === null || $user->id === null) {
            $this->session->remove('authenticated_user');

            return null;
        }

        return new AuthenticatedUser($user->id, $user->email, $user->role->value);
    }
}
