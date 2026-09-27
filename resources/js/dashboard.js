import { BarController, BarElement, CategoryScale, Chart, LinearScale, Tooltip } from 'chart.js';
import { initSearchableSelects } from './searchable-select';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
const INK = { primary: '#0f172a', secondary: '#475569', muted: '#64748b', grid: '#e8eae6' };

/**
 * Grafik batang horizontal satu seri per wilayah (kecamatan/kelurahan).
 * Satu warna untuk semua batang; nilai tersedia lewat tooltip dan tab "Tabel".
 *
 * data-area-chart = { labels, values, urls, color, unit, drill: 'district'|'village', label }
 */
function initAreaChart(canvas) {
    const config = JSON.parse(canvas.dataset.areaChart);

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: config.labels,
            datasets: [
                {
                    label: config.label,
                    data: config.values,
                    backgroundColor: config.color,
                    hoverBackgroundColor: config.hoverColor ?? config.color,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 20,
                    categoryPercentage: 0.8,
                    barPercentage: 0.9,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 400 },
            layout: { padding: { right: 8 } },
            interaction: { mode: 'nearest', axis: 'y', intersect: false },
            scales: {
                x: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: INK.grid, lineWidth: 1 },
                    ticks: {
                        color: INK.muted,
                        font: { size: 11 },
                        maxTicksLimit: 6,
                        callback: (value) => numberFormat.format(value),
                    },
                    title: { display: true, text: config.unit, color: INK.muted, font: { size: 11 } },
                },
                y: {
                    border: { color: '#cbd5e1' },
                    grid: { display: false },
                    ticks: {
                        color: INK.secondary,
                        font: { size: 12, weight: 500 },
                        autoSkip: false,
                        callback(value) {
                            const label = this.getLabelForValue(value);
                            return label.length > 22 ? `${label.slice(0, 21)}…` : label;
                        },
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    titleColor: INK.muted,
                    titleFont: { size: 12, weight: 500 },
                    bodyColor: INK.primary,
                    bodyFont: { size: 14, weight: 700 },
                    footerColor: INK.muted,
                    footerFont: { size: 11, weight: 400 },
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `${numberFormat.format(context.raw)} ${config.unit}`,
                        footer: () => (config.drill === 'district' ? 'Klik untuk melihat per kelurahan' : 'Klik untuk melihat rincian kelompok'),
                    },
                },
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            },
            onClick: (event, elements) => {
                if (!elements.length) return;
                const index = elements[0].index;
                const url = config.urls[index];

                if (config.drill === 'district') {
                    window.location.assign(url);
                } else {
                    window.dispatchEvent(
                        new CustomEvent('open-detail', { detail: { url, title: `${config.detailTitle} — Kel. ${config.labels[index]}` } }),
                    );
                }
            },
        },
    });
}

document.querySelectorAll('[data-area-chart]').forEach(initAreaChart);

initSearchableSelects();
