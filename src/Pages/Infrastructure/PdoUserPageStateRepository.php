<?php

declare(strict_types=1);

namespace App\Pages\Infrastructure;

use App\Pages\Domain\UserPageStateRepositoryInterface;
use DateTimeImmutable;
use PDO;

final class PdoUserPageStateRepository implements UserPageStateRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function exists(int $userId, string $page, string $action): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1
             FROM user_page_states
             WHERE user_id = :user_id AND page = :page AND action = :action
             LIMIT 1',
        );
        $statement->execute([
            'user_id' => $userId,
            'page' => $page,
            'action' => $action,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function addIfMissing(int $userId, string $page, string $action, DateTimeImmutable $createdAt): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO user_page_states (user_id, page, action, created_at)
             VALUES (:user_id, :page, :action, :created_at)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)',
        );
        $statement->execute([
            'user_id' => $userId,
            'page' => $page,
            'action' => $action,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);

        return $statement->rowCount() === 1;
    }
}
