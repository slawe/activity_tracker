<?php

declare(strict_types=1);

use App\Shared\Kernel\Database\Connection;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @return array{host: string, port: int, database: string, username: string, password: string}
 */
function databaseConfig(string $projectRoot): array
{
    /** @var array{host: string, port: int, database: string, username: string, password: string} $config */
    $config = require $projectRoot . '/config/database.php';

    return $config;
}

$projectRoot = dirname(__DIR__);
$pdo = Connection::create(databaseConfig($projectRoot));
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(255) PRIMARY KEY,
        executed_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
);

$executedStatement = $pdo->query('SELECT migration FROM schema_migrations');
$executed = array_fill_keys($executedStatement->fetchAll(PDO::FETCH_COLUMN), true);
$migrationFiles = glob($projectRoot . '/database/migrations/*.sql') ?: [];
sort($migrationFiles, SORT_STRING);

foreach ($migrationFiles as $migrationFile) {
    $migration = basename($migrationFile);
    if (isset($executed[$migration])) {
        echo sprintf("Skipping %s (already executed).\n", $migration);
        continue;
    }

    $sql = file_get_contents($migrationFile);
    if ($sql === false) {
        throw new RuntimeException(sprintf('Unable to read migration: %s', $migration));
    }

    echo sprintf("Running %s...\n", $migration);
    $pdo->beginTransaction();

    try {
        $pdo->exec($sql);
        $statement = $pdo->prepare(
            'INSERT INTO schema_migrations (migration, executed_at) VALUES (:migration, :executed_at)',
        );
        $statement->execute([
            'migration' => $migration,
            'executed_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    echo sprintf("Completed %s.\n", $migration);
}

echo "Migrations are up to date.\n";
