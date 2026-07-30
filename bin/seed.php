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
$users = [
    ['email' => 'admin@example.com', 'role' => 'admin'],
    ['email' => 'user@example.com', 'role' => 'user'],
];

$statement = $pdo->prepare(
    'INSERT INTO users (email, password_hash, role, created_at)
     VALUES (:email, :password_hash, :role, :created_at)
     ON DUPLICATE KEY UPDATE email = VALUES(email)',
);

foreach ($users as $user) {
    $statement->execute([
        'email' => $user['email'],
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => $user['role'],
        'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
    ]);

    echo sprintf("Seeded %s.\n", $user['email']);
}

echo "Seed data is up to date.\n";
