<?php

declare(strict_types=1);

use App\Shared\Kernel\Application;

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

return Application::boot(dirname(__DIR__));
