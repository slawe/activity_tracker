'use strict';

(() => {
    const namespace = 'http://www.w3.org/2000/svg';
    const width = 900;
    const height = 360;
    const padding = 45;
    const series = [
        ['pageAViews', '#2563eb', 'Page A views'],
        ['pageBViews', '#16a34a', 'Page B views'],
        ['buyCowClicks', '#dc2626', 'Buy a cow clicks'],
        ['downloadClicks', '#9333ea', 'Download clicks'],
    ];

    function readReportRows() {
        const dataElement = document.getElementById('report-data');

        return dataElement ? JSON.parse(dataElement.textContent || '[]') : null;
    }

    function createSvgElement(name, attributes = {}) {
        const element = document.createElementNS(namespace, name);
        Object.entries(attributes).forEach(([attribute, value]) => {
            element.setAttribute(attribute, String(value));
        });

        return element;
    }

    function drawEmptyState(chart) {
        const message = createSvgElement('text', {
            x: width / 2,
            y: height / 2,
            'text-anchor': 'middle',
        });
        message.textContent = 'No data for this period';
        chart.appendChild(message);
    }

    function drawAxis(chart) {
        chart.appendChild(createSvgElement('path', {
            d: `M ${padding} ${padding} V ${height - padding} H ${width - padding}`,
            class: 'chart-axis',
        }));
    }

    function drawYAxisLabels(chart, maximum, y) {
        for (let tick = 0; tick <= 4; tick += 1) {
            const value = Math.round((maximum * tick) / 4);
            const label = createSvgElement('text', {
                x: padding - 8,
                y: y(value) + 4,
                'text-anchor': 'end',
                class: 'chart-label',
            });
            label.textContent = String(value);
            chart.appendChild(label);
        }
    }

    function drawDateLabels(chart, rows, x) {
        const labelStep = Math.max(1, Math.ceil(rows.length / 8));

        rows.forEach((row, index) => {
            if (index % labelStep !== 0 && index !== rows.length - 1) {
                return;
            }

            const label = createSvgElement('text', {
                x: x(index),
                y: height - padding + 20,
                'text-anchor': 'middle',
                class: 'chart-label',
            });
            label.textContent = row.date;
            chart.appendChild(label);
        });
    }

    function drawPointMarkers(chart, rows, definition, seriesIndex, x, y) {
        const [key, color, label] = definition;

        rows.forEach((row, index) => {
            const singleDateOffset = rows.length === 1 ? (seriesIndex - 1.5) * 16 : 0;
            const point = createSvgElement('circle', {
                cx: x(index) + singleDateOffset,
                cy: y(row[key]),
                r: rows.length === 1 ? 7 : 5,
                fill: '#fff',
                stroke: color,
                'stroke-width': 3,
            });
            const title = createSvgElement('title');
            title.textContent = `${label}: ${row[key]} on ${row.date}`;
            point.appendChild(title);
            chart.appendChild(point);
        });
    }

    function drawSeries(chart, rows, x, y) {
        series.forEach((definition, seriesIndex) => {
            const [key, color] = definition;
            chart.appendChild(createSvgElement('polyline', {
                points: rows.map((row, index) => `${x(index)},${y(row[key])}`).join(' '),
                fill: 'none',
                stroke: color,
                'stroke-width': 3,
            }));
            drawPointMarkers(chart, rows, definition, seriesIndex, x, y);
        });
    }

    function renderChart() {
        const chart = document.getElementById('activity-chart');
        const rows = readReportRows();
        if (!chart || rows === null) {
            return;
        }
        if (rows.length === 0) {
            drawEmptyState(chart);
            return;
        }

        const maximum = Math.max(1, ...rows.flatMap((row) => series.map(([key]) => row[key])));
        const x = (index) => rows.length === 1
            ? width / 2
            : padding + (index * (width - 2 * padding)) / (rows.length - 1);
        const y = (value) => height - padding - (value * (height - 2 * padding)) / maximum;

        drawAxis(chart);
        drawYAxisLabels(chart, maximum, y);
        drawDateLabels(chart, rows, x);
        drawSeries(chart, rows, x, y);
    }

    renderChart();
})();
