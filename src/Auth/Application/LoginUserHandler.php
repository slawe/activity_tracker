<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Auth\Domain\UserRepositoryInterface;
use App\Shared\Kernel\Security\PasswordHasher;
use App\Shared\Kernel\Security\Session;
use DomainException;

final class LoginUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasher $passwordHasher,
        private readonly Session $session,
        private readonly ActivityTracker $activityTracker,
    ) {
    }

    public function handle(LoginUserCommand $command): void
    {
        $user = $this->users->findByEmail(strtolower(trim($command->email)));

        if ($user === null || !$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            throw new DomainException('Invalid email or password.');
        }

        $this->activityTracker->track(new TrackActivityCommand(
            $user->id,
            ActivityAction::Login,
            null,
            null,
            $command->ipAddress,
            $command->userAgent,
        ));
        $this->session->regenerate();
        $this->session->set('authenticated_user', ['id' => $user->id]);
    }
}
