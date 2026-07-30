<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure;

use App\Auth\Domain\User;
use App\Auth\Domain\UserAlreadyExistsException;
use App\Auth\Domain\UserRepositoryInterface;
use App\Auth\Domain\UserRole;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;

final class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function findById(int $id): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT id, email, password_hash, role, created_at FROM users WHERE id = :id LIMIT 1',
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT id, email, password_hash, role, created_at FROM users WHERE email = :email LIMIT 1',
        );
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function add(User $user): User
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (email, password_hash, role, created_at)
             VALUES (:email, :password_hash, :role, :created_at)',
        );
        try {
            $statement->execute([
                'email' => $user->email,
                'password_hash' => $user->passwordHash,
                'role' => $user->role->value,
                'created_at' => $user->createdAt->format('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $exception) {
            if ($this->isDuplicateEmailError($exception)) {
                throw new UserAlreadyExistsException();
            }

            throw $exception;
        }

        $id = (int) $this->connection->lastInsertId();
        if ($id < 1) {
            throw new RuntimeException('Unable to determine the created user ID.');
        }

        return new User($id, $user->email, $user->passwordHash, $user->role, $user->createdAt);
    }

    private function isDuplicateEmailError(PDOException $exception): bool
    {
        $driverCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : null;

        return $exception->getCode() === '23000' && $driverCode === 1062;
    }

    /**
     * @param array<string, mixed> $row
     * @throws \DateMalformedStringException
     */
    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['email'],
            (string) $row['password_hash'],
            UserRole::from((string) $row['role']),
            new DateTimeImmutable((string) $row['created_at']),
        );
    }
}
