/**
 * Alpine.js — self-hosted (no CDN) untuk halaman admin DLH.
 *
 * Task 4: hilangkan dependency 4 script dari cdn.jsdelivr.net di setiap halaman
 * admin (full-page-reload, jadi 4 request eksternal berulang per navigasi).
 *
 * Catatan penting soal double-init:
 *  - Halaman publik memakai Alpine yang DIBUNDEL Livewire (`@livewireScripts`).
 *    Karena itu Alpine hanya di-bootstrap pada halaman yang menandai dirinya
 *    dengan atribut `data-alpine-bootstrap` di <body> (manifest admin & login).
 *  - `window.Alpine` diisi agar inline `<script>` lama yang memakai
 *    `document.addEventListener('alpine:init', () => Alpine.store(...))`
 *    tetap bekerja (contoh: store 'petaSidebar' di admin/peta).
 */
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

function bootstrapAlpine() {
    if (window.__dlhAlpineStarted) return;
    window.__dlhAlpineStarted = true;

    Alpine.plugin(focus);
    Alpine.plugin(collapse);
    Alpine.plugin(intersect);

    // Store 'sidebar' — dipindahkan dari inline script layouts/admin.blade.php.
    // Terdaftar lewat event `alpine:init` seperti sebelumnya.
    document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
            collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
            toggle() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebar-collapsed', this.collapsed);
            },
        });
    });

    window.Alpine = Alpine;
    Alpine.start();
}

function shouldBootstrap() {
    return document.documentElement.hasAttribute('data-alpine-bootstrap')
        || (document.body && document.body.hasAttribute('data-alpine-bootstrap'));
}

if (shouldBootstrap()) {
    bootstrapAlpine();
} else {
    // Body belum tersedia (modul dieksekusi sebelum parse selesai di kasus tertentu).
    document.addEventListener('DOMContentLoaded', () => {
        if (shouldBootstrap()) bootstrapAlpine();
    });
}