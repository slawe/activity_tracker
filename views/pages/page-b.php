<?php

declare(strict_types=1);

/** @var string $csrfToken */
?>
<section class="card">
    <h1>Page B</h1>
    <form method="post" action="/page-b/download">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Download</button>
    </form>
</section>
