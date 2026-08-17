// Self-hosted Chart.js (menggantikan CDN chart.umd.min.js).
// Di-bundle & di-defer oleh Vite → tidak lagi mem-block parser/render (LCP).
import Chart from 'chart.js/auto';

window.Chart = Chart;
