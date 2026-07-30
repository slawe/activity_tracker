<?php

declare(strict_types=1);

/** @var string $csrfToken */
/** @var string|null $error */
?>
<section class="auth-card">
    <h1>Login</h1>
    <?php if ($error !== null): ?>
        <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="/login">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <label>
            Email
            <input type="email" name="email" required autocomplete="email">
        </label>
        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit">Login</button>
    </form>
</section>
