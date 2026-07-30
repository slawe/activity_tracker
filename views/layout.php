<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $title */
/** @var \App\Shared\Kernel\Security\AuthenticatedUser|null $currentUser */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Activity Tracker', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="brand">Activity Tracker</a>
        <nav>
            <?php if (($currentUser ?? null) !== null): ?>
                <a href="/page-a">Page A</a>
                <a href="/page-b">Page B</a>
                <?php if ($currentUser->isAdmin()): ?>
                    <a href="/admin/stats">Statistics</a>
                    <a href="/admin/reports">Reports</a>
                <?php endif; ?>
                <form method="post" action="/logout" class="inline-form">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="link-button">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container">
        <?= $content ?>
    </main>
</body>
</html>
