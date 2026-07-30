<?php

declare(strict_types=1);

return [
    'host' => getenv('DB_HOST') ?: 'mysql',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'activity_tracker',
    'username' => getenv('DB_USERNAME') ?: 'activity_tracker',
    'password' => getenv('DB_PASSWORD') ?: 'activity_tracker',
];
