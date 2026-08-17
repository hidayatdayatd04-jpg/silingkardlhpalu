// Self-hosted Flatpickr (menggantikan CDN flatpickr.min.js + .css).
// Di-bundle & di-defer oleh Vite → tidak lagi mem-block parser/render (LCP).
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.flatpickr = flatpickr;
