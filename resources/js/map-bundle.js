/**
 * map-bundle.js — chunk khusus halaman yang benar-benar menampilkan peta.
 *
 * Task 5: kelas-kelas MapLibre control (`DlhZoomControl`, `DlhBasemapSwitcher`,
 * `DlhWeatherControl`, dst.) dan `DlhMarkers` dipindahkan Keluar dari app.js
 * (yang dimuat di SEMUA halaman) supaya halaman tabel/dashboard tanpa peta tidak
 * perlu parse ~1.300 baris JS peta.
 *
 * Entry ini di-load dengan dua cara:
 *  1. Eager : `@vite('resources/js/map-bundle.js')` di view yang punya peta.
 *  2. Fallback dinamis : `import('./map-bundle')` dari app.js (ensureMaplibreLoaded /
 *     ensureLeafletLoaded) — dijamin chunk ini selalu ada sebelum map di-create.
 */
import './map-components';
import './dlh-markers';