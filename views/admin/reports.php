<?php

declare(strict_types=1);

use App\Reporting\Domain\DailyActivityReport;

/** @var list<DailyActivityReport> $reports */
/** @var list<array<string, int|string>> $chartData */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var string $today */
?>
<h1>Daily Reports</h1>
<form method="get" class="filters">
    <label>Date from <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>"></label>
    <label>Date to <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>"></label>
    <button type="submit">Apply</button>
</form>
<div class="chart-container">
    <svg id="activity-chart" viewBox="0 0 900 360" role="img" aria-label="Daily activity graph"></svg>
    <ul class="chart-legend" aria-label="Graph legend">
        <li><span class="chart-swatch chart-swatch-page-a"></span>Page A views</li>
        <li><span class="chart-swatch chart-swatch-page-b"></span>Page B views</li>
        <li><span class="chart-swatch chart-swatch-buy-cow"></span>Buy a cow clicks</li>
        <li><span class="chart-swatch chart-swatch-download"></span>Download clicks</li>
    </ul>
</div>
<script type="application/json" id="report-data"><?= json_encode(
    $chartData,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
) ?></script>
<script src="/assets/reports.js" defer></script>
<div class="table-scroll">
    <table>
        <thead>
        <tr><th>Date</th><th>Page A views</th><th>Page B views</th><th>Buy a cow clicks</th><th>Download clicks</th></tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report->date->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $report->pageAViews ?></td>
                <td><?= $report->pageBViews ?></td>
                <td><?= $report->buyCowClicks ?></td>
                <td><?= $report->downloadClicks ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
