<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$application = require dirname(__DIR__) . '/config/app.php';
$application->run();
