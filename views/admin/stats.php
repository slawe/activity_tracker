<?php

declare(strict_types=1);

use App\Activity\Application\ActivitySearchResult;
use App\Activity\Domain\ActivityAction;

/** @var ActivitySearchResult $result */
/** @var list<ActivityAction> $actions */
/** @var array{date_from: string, date_to: string, user_email: string, action: string} $filters */
/** @var string $today */

$paginationQuery = static function (int $page) use ($filters): string {
    return http_build_query([...$filters, 'page' => $page]);
};
?>
<h1>Activity Statistics</h1>
<form method="get" class="filters">
    <label>Date from <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>"></label>
    <label>Date to <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>"></label>
    <label>User email
        <input type="email" name="user_email" value="<?= htmlspecialchars($filters['user_email'], ENT_QUOTES, 'UTF-8') ?>">
    </label>
    <label>Action
        <select name="action">
            <option value="">All</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?= $action->value ?>" <?= $filters['action'] === $action->value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($action->value, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filter</button>
</form>
<p><?= $result->total ?> event(s)</p>
<div class="table-scroll">
    <table>
        <thead>
        <tr><th>Date</th><th>User</th><th class="activity-action">Action</th><th>Page</th><th>Target</th><th>IP</th><th>User agent</th></tr>
        </thead>
        <tbody>
        <?php foreach ($result->events as $event): ?>
            <tr>
                <td><?= htmlspecialchars($event->createdAt->format('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($event->userEmail ?? 'guest', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="activity-action"><?= htmlspecialchars($event->action->value, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($event->page?->value ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($event->target?->value ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($event->ipAddress, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($event->userAgent, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<nav class="pagination" aria-label="Pagination">
    <?php if ($result->page > 1): ?>
        <a href="?<?= htmlspecialchars($paginationQuery($result->page - 1), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
    <?php endif; ?>
    <span>Page <?= $result->page ?> of <?= $result->totalPages() ?></span>
    <?php if ($result->page < $result->totalPages()): ?>
        <a href="?<?= htmlspecialchars($paginationQuery($result->page + 1), ENT_QUOTES, 'UTF-8') ?>">Next</a>
    <?php endif; ?>
</nav>
