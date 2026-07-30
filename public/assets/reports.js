'use strict';

(() => {
    const dataElement = document.getElementById('report-data');
    const chart = document.getElementById('activity-chart');
    if (!dataElement || !chart) {
        return;
    }

    const rows = JSON.parse(dataElement.textContent || '[]');
    if (rows.length === 0) {
        chart.innerHTML = '<text x="450" y="180" text-anchor="middle">No data for this period</text>';
        return;
    }

    const series = [
        ['pageAViews', '#2563eb', 'Page A views'],
        ['pageBViews', '#16a34a', 'Page B views'],
        ['buyCowClicks', '#dc2626', 'Buy a cow clicks'],
        ['downloadClicks', '#9333ea', 'Download clicks'],
    ];
    const width = 900;
    const height = 360;
    const padding = 45;
    const maximum = Math.max(1, ...rows.flatMap((row) => series.map(([key]) => row[key])));
    const x = (index) => rows.length === 1
        ? width / 2
        : padding + (index * (width - 2 * padding)) / (rows.length - 1);
    const y = (value) => height - padding - (value * (height - 2 * padding)) / maximum;
    const namespace = 'http://www.w3.org/2000/svg';

    const axis = document.createElementNS(namespace, 'path');
    axis.setAttribute('d', `M ${padding} ${padding} V ${height - padding} H ${width - padding}`);
    axis.setAttribute('class', 'chart-axis');
    chart.appendChild(axis);

    for (let tick = 0; tick <= 4; tick += 1) {
        const value = Math.round((maximum * tick) / 4);
        const label = document.createElementNS(namespace, 'text');
        label.setAttribute('x', padding - 8);
        label.setAttribute('y', y(value) + 4);
        label.setAttribute('text-anchor', 'end');
        label.setAttribute('class', 'chart-label');
        label.textContent = String(value);
        chart.appendChild(label);
    }

    const labelStep = Math.max(1, Math.ceil(rows.length / 8));
    rows.forEach((row, index) => {
        if (index % labelStep !== 0 && index !== rows.length - 1) {
            return;
        }

        const label = document.createElementNS(namespace, 'text');
        label.setAttribute('x', x(index));
        label.setAttribute('y', height - padding + 20);
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('class', 'chart-label');
        label.textContent = row.date;
        chart.appendChild(label);
    });

    series.forEach(([key, color, label], seriesIndex) => {
        const line = document.createElementNS(namespace, 'polyline');
        line.setAttribute('points', rows.map((row, index) => `${x(index)},${y(row[key])}`).join(' '));
        line.setAttribute('fill', 'none');
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', '3');
        chart.appendChild(line);

        rows.forEach((row, index) => {
            const point = document.createElementNS(namespace, 'circle');
            const singleDateOffset = rows.length === 1 ? (seriesIndex - 1.5) * 16 : 0;
            point.setAttribute('cx', x(index) + singleDateOffset);
            point.setAttribute('cy', y(row[key]));
            point.setAttribute('r', rows.length === 1 ? '7' : '5');
            point.setAttribute('fill', '#fff');
            point.setAttribute('stroke', color);
            point.setAttribute('stroke-width', '3');

            const title = document.createElementNS(namespace, 'title');
            title.textContent = `${label}: ${row[key]} on ${row.date}`;
            point.appendChild(title);
            chart.appendChild(point);
        });
    });
})();
