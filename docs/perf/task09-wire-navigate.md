# Task 9 — Evaluasi & PoC `wire:navigate` untuk navigasi Admin

> Status: **PoC terisolasi selesai** — belum diaktifkan di layout admin utama.
> Keputusan rollout penuh menunggu persetujuan tim/owner (lihat bagian Akhir).

## Latar belakang

Proyek sudah memakai `livewire/livewire ^4.3` tapi navigasi antar-halaman admin
masih *full-page-reload* (`<a href>` native). Setiap klik menu → browser reload
total: HTML, CSS, dan seluruh JS (termasuk Alpine + Vite bundle) di-parse ulang.
Livewire menyediakan `wire:navigate` (SPA-like) supaya halaman berikutnya
di-swap lewat `fetch()` — JS/CSS di `<head>` dievaluasi sekali, sehingga navigasi
terasa jauh lebih cepat.

## Temuan teknis penting (hasil riset di environment ini)

1. **Livewire 4.3.3 MENGIRIM Alpine sendiri.**
   - `vendor/livewire/livewire/dist/livewire.esm.js` berisi Alpine
     (`node_modules/alpinejs`, `Alpine: () => src_default2`, import dari
     `alpinejs`...), dan `@livewireScripts` otomatis menginjeksi
     **Alpine baru** tiap halaman.
   - Layout admin sekarang (Task 4) memakai **Alpine self-hosted** dari
     `resources/js/alpine.js` (`window.Alpine = Alpine; Alpine.start()`).
   - **Konsekuensi:** menambahkan `@livewireScripts` ke `layouts/admin.blade.php`
     = **dua instance Alpine** → peringatan *"Alpine Already Initialized"*,
     `x-*/x-init` ganda, dan Store/plugin tidak sinkron. **Ini regresi Task 4.**

2. **Script di dalam `<body>` dievaluasi ulang** saat Livewire swap halaman
   (dokumentasi Navigate: *"Scripts in the <body> are re-evaluated"*).
   `data-navigate-once` → hanya jalan di kunjungan pertama.
   Ini berarti `@push('scripts')` tiap halaman tetap akan berjalan — **tapi**:

3. **Jangan bergantung pada `DOMContentLoaded`.** Livewire navigate tidak men-
   trigger event itu pada pindahan berikutnya (browser tidak me-load halaman baru).
   Semua inline-script admin yang memakai `document.addEventListener('DOMContentLoaded', ...)`
   perlu diganti dengan pola *replay-safe* (lihat checklist di bawah) sebelum
   halaman tsb masuk pool navigasi SPA.

4. **Halaman publik (layouts/app) sudah memakai Livewire** (`@livewireScripts`),
   sehingga eskalasi berikutnya bisa berangkat dari menu publik yang sudah aman
   (navbar sudah Alpine dari Livewire).

## PoC yang dikirim (terisolasi, tanpa regresi)

- **Rute** (hanya `APP_ENV=local`):
  - `GET /admin/navigate-poc` → `admin.navigate-poc.index`
  - `GET /admin/navigate-poc/item/{n}` → `admin.navigate-poc.show`
- **Layout:** `resources/views/layouts/admin-navigate-poc.blade.php`
  - memuat `@vite(['resources/css/app.css'])` **(tak ada `app.js`)** sehingga
    tanpa Alpine kedua; lalu `@livewireStyles` + `@livewireScripts`.
  - Semua link memakai `wire:navigate` (kecuali tombol "Item 3" sbg pembanding
    full-reload).
- **Yang bisa dicoba:**
  1. `php artisan serve` (atau Vite dev) lalu login admin & buka `/admin/navigate-poc`.
  2. Klik Index → Item 1 → Item 2 → kembali. DevTools:
     - Tab **Network** → navigasi antar-ponsel = `fetch` 200 (bukan reload),
       tidak ada re-download asset `app.js`/CSS (cache).
     - Perhatikan penghitung *script body* bertambah; penghitung
       `data-navigate-once` tetap 1.
     - Counter Alpine menandakan **state komponen di-reset di tiap pindah halaman**
       (perilaku normal — dokumentasi Livewire menyarankan `@persist` untuk state
       yang mesti bertahan).
  3. Bandingkan klik "Item 3" (full reload) vs "Item 2" (SPA) — dapat dilihat
     perbedaan TTFB/perubahan visual.

## Jangan lakukan (pengingat untuk tim)

- ❌ Jangan menambahkan `@livewireScripts` ke `layouts/admin.blade.php` selama
  admin masih memakai Alpine self-hosted dari `app.js`.
- ✅ Jika ingin Livewire full di admin, jalur yang benar adalah **unifikasi Alpine**:
  ```
  // resources/js/alpine.js (kontrak baru)
  import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
  // daftarkan plugin focus/collapse/intersect + store sidebar di instance ini
  window.Alpine = Alpine;  Livewire.start();
  ```
  dan ganti `@livewireScripts` dengan `@livewireScriptConfig` di layout.
  (Ini di luar lingkup PoC ini — butuh pengujian menyeluruh 2–3 halaman.)

## Roadmap rollout bertahap (usulan)

1. **Fase 1 (2–3 halaman sederhana).** Pilih halaman yang **bebas
   `DOMContentLoaded`**: semisal `admin/activity-log/index`, `admin/notifications/index`,
   `admin/backup/index`. Pindahkan `@vite`/layout ke pola SPA + `wire:navigate`
   di link internalnya. Ukur: query count tetap, interaksi normal.
2. **Tulis adapter script.** Tambahkan helper di `resources/js/admin-common.js`:
   `runOnNavigated(fn)` yang memanggil `fn()` saat `DOMContentLoaded` DAN saat
   `livewire:navigated` (yang terakhir untuk pengganti). Refactor satu per satu
   `document.addEventListener('DOMContentLoaded', ...)` di halaman yang di-SPA-kan.
3. **Ukur benefit**: Pantau tab Network (jumlah request aset & payload) dan
   TTFB untuk `/admin/pengaduan-pengendalian` sebelum/sesudah.
4. **Perluas rollout** hanya setelah tidak ada regresi map/chart/jodit/form upload.

### Kriteria "selesai" untuk Task 9 (DoD)

- [ ] PoC jalan di environment local (routes di atas).
- [x] Tidak ada double-Alpine / peringatan *"Alpine Already Initialized"* di
      halaman PoC.
- [ ] (setelah Fase 1) 2–3 halaman admin menampilkan jaringan fetch tanpa
      re-parse app.js dan semua fitur halaman itu normal.

## File yang menyusun PoC ini

- `routes/web.php` → blok poC (local-only)
- `resources/views/layouts/admin-navigate-poc.blade.php`
- `resources/views/admin/navigate-poc/index.blade.php`, `show.blade.php`, `_nav.blade.php`