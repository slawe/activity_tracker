<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Activity\Application\ActivityTracker;
use App\Activity\Application\TrackActivityCommand;
use App\Activity\Domain\ActivityAction;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use App\Auth\Domain\UserRole;
use App\Auth\Domain\UserAlreadyExistsException;
use App\Shared\Kernel\Database\TransactionManager;
use App\Shared\Kernel\Security\PasswordHasher;
use DateTimeImmutable;
use DomainException;

final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasher $passwordHasher,
        private readonly ActivityTracker $activityTracker,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function handle(RegisterUserCommand $command): void
    {
        $email = strtolower(trim($command->email));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Enter a valid email address.');
        }

        $passwordLength = strlen($command->password);
        if ($passwordLength < 8 || $passwordLength > 72) {
            throw new DomainException('Password must contain between 8 and 72 bytes.');
        }

        if ($this->users->findByEmail($email) !== null) {
            throw new UserAlreadyExistsException();
        }

        $passwordHash = $this->passwordHasher->hash($command->password);
        $createdAt = new DateTimeImmutable();

        $this->transactions->run(function () use ($command, $email, $passwordHash, $createdAt): void {
            $user = $this->users->add(new User(
                null,
                $email,
                $passwordHash,
                UserRole::User,
                $createdAt,
            ));

            $this->activityTracker->track(new TrackActivityCommand(
                $user->id,
                ActivityAction::Registration,
                null,
                null,
                $command->ipAddress,
                $command->userAgent,
            ));
        });
    }
}
