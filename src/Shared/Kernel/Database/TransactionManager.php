<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Database;

use PDO;
use Throwable;

final class TransactionManager
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function run(callable $operation): mixed
    {
        if ($this->connection->inTransaction()) {
            return $operation();
        }

        $this->connection->beginTransaction();

        try {
            $result = $operation();
            $this->connection->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
