<?php

declare(strict_types=1);

/** @var string $csrfToken */
/** @var bool $hasBoughtCow */
?>
<section class="card">
    <h1>Page A</h1>
    <?php if ($hasBoughtCow): ?>
        <p class="success">thankYou</p>
    <?php else: ?>
        <form method="post" action="/page-a/buy-cow">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Buy a cow</button>
        </form>
    <?php endif; ?>
</section>
