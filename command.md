# Panduan Perintah & Setup Project SILINGKAR (DLH Kota Palu)

Dokumen ini menjelaskan semua perintah yang perlu dijalankan saat pertama kali setup project ini di komputer/server baru, dan perintah apa saja yang harus terus berjalan selama development supaya semua fitur (email, GPS tracking, jadwal otomatis) berfungsi.


analisa terlebih dahulu seluruh project lalu kerjakan agar kamu paham dengan benar dulu dan jika ada pertanyaan mohon tanyakan aja dan tolong berbahasa indonesia

---

## 1. Setup Awal (Baru Clone Project / Server Baru)

Jalankan berurutan dari atas ke bawah. Jangan lompat step, karena beberapa step bergantung ke step sebelumnya.

| No | Perintah | Fungsi |
|----|----------|--------|
| 1 | `composer install` | Download semua dependency PHP/Laravel yang dipakai project (ada di `composer.json`) |
| 2 | `cp .env.example .env` | Salin template konfigurasi environment jadi file `.env` asli (file ini yang dibaca Laravel, dan tidak ikut ter-commit ke Git) |
| 3 | `php artisan key:generate` | Generate `APP_KEY` unik untuk enkripsi session/cookie — wajib, tanpa ini aplikasi akan error |
| 4 | **Edit file `.env`** | Isi kredensial yang masih kosong — lihat tabel bagian 2 di bawah, jangan sampai ada yang kelewat |
| 5 | `php artisan dlh:setup-seeder --fresh` | **Command khusus project ini** — sekali jalan otomatis: bikin folder storage, bikin file placeholder gambar, bikin symbolic link storage, migrasi database dari nol, dan seeding data + akun default. Ini cara tercepat & paling disarankan untuk setup awal (lihat detail di bagian 4). |
| 6 | `npm install` | Download semua dependency JS/frontend (Tailwind, Alpine.js, dll — ada di `package.json`) |
| 7 | `npm run build` | Compile & minify CSS/JS untuk mode production. Untuk development sehari-hari, pakai `npm run dev` saja (auto-reload saat file diubah, lihat bagian 3). |

> **Catatan:** kalau tidak mau pakai `dlh:setup-seeder`, bisa juga manual: `php artisan migrate` (bikin tabel kosong) lalu `php artisan db:seed` (isi data dummy) dan `php artisan storage:link` (buat symbolic link) secara terpisah — tapi lebih ribet dan gampang ada step yang kelewat, jadi lebih baik pakai `dlh:setup-seeder`.

### Isian Wajib di File `.env`

Aplikasi tetap bisa jalan tanpa isi bagian ini, tapi fitur terkait tidak akan berfungsi kalau kosong/masih nilai contoh:

| Variabel | Fungsi | Wajib untuk fitur |
|----------|--------|--------------------|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi ke database MySQL lokal | Semua fitur (dasar aplikasi) |
| `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` (pakai SMTP Brevo) | Kirim email notifikasi tiket pengaduan/permohonan (pengganti WhatsApp yang sudah dihapus) | Notifikasi email ke pelapor & admin |
| `GPS_LOGIN_URL`, `GPS_MONITORING_URL`, `GPS_USERNAME`, `GPS_PASSWORD` | Login ke API GPS.id untuk tracking lokasi armada | Peta monitoring GPS armada (fleet tracking) |
| `OPENROUTER_API_KEY` | Akses model AI lewat OpenRouter | Chatbot AI di halaman publik |

---

## 2. Kredensial Login Default (Hasil Seeder)

Setelah `dlh:setup-seeder` (atau `db:seed`) berhasil, akun berikut otomatis tersedia untuk login ke admin panel:

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin (akses penuh semua bidang) |
| `pengendalian` | `pengendalian123` | Admin Pengendalian |
| `sampah-lb3` | `sampah123` | Admin Sampah & LB3 |
| `tata-penataan` | `tata123` | Admin Tata Penataan |
| `rth` | `rth123` | Admin RTH |

> Ganti password ini sebelum project dipakai serius/di-deploy ke server publik — ini murni akun default untuk kebutuhan development & testing.

---

## 3. Perintah yang Harus Terus Berjalan Selama Development

Ini penjelasan lengkap untuk 4 perintah yang ada di `perintah.md` sebelumnya. Idealnya tiap perintah ini dijalankan di **terminal/tab terpisah** dan dibiarkan menyala terus selama kamu buka/develop aplikasinya (jangan di-Ctrl+C kecuali memang mau berhenti kerja).

| Perintah | Fungsi | Kapan wajib dipakai | Catatan |
|----------|--------|----------------------|---------|
php artisan queue:work
| `php artisan queue:listen --tries=3` | Menjalankan worker antrian (queue) yang otomatis reload kalau ada perubahan kode, memproses job seperti `SendEmailNotificationJob` (pengiriman email notifikasi tiket secara async/di background) | **Dipakai saat development**, karena kode masih sering berubah | Sedikit lebih lambat dari `queue:work` karena boot ulang tiap job, tapi aman dipakai sambil coding karena selalu baca kode terbaru |
| `php artisan queue:work --tries=3 --timeout=60` | Sama-sama menjalankan worker antrian, tapi **tidak** otomatis reload kode — proses PHP tetap sama sampai di-restart manual | **Dipakai di server production**, lebih cepat & hemat resource dibanding `queue:listen` | Kalau ada deploy/update kode di production, wajib `php artisan queue:restart` supaya worker baca kode baru |
| `php artisan schedule:work` | Menjalankan scheduler Laravel secara terus-menerus di lokal (mensimulasikan cron job tiap menit) — ini yang **men-trigger `gps:fetch` otomatis setiap 30 detik** sesuai jadwal di `routes/console.php` | **Wajib jalan** kalau mau data GPS armada ter-update otomatis tanpa perlu jalankan `gps:fetch` manual berulang-ulang | Di server production biasanya tidak pakai ini, tapi daftarkan 1 baris cron job (`* * * * * php artisan schedule:run`) di crontab server |
| `php artisan gps:fetch` | Menjalankan **sekali** proses fetch & cache lokasi GPS armada dari API GPS.id (`GpsService`) | Dipakai manual kalau cuma mau test/cek koneksi API GPS tanpa perlu nunggu scheduler, atau kalau `schedule:work` tidak dijalankan | Kalau `schedule:work` sudah jalan, command ini **tidak perlu** dijalankan manual lagi karena sudah otomatis tiap 30 detik |

---

## 4. Cara Lebih Simpel: 1 Perintah untuk Semua (Development)

Project ini sudah dikonfigurasi supaya development sehari-hari bisa cukup **1 perintah saja** di 1 terminal, tanpa perlu buka banyak tab manual:

```bash
composer run dev
```

Perintah ini menjalankan **bersamaan** (lewat `concurrently`) dan diberi warna beda per proses di terminal yang sama:
- `php artisan serve` — server lokal Laravel
- `php artisan queue:listen --tries=1 --timeout=0` — worker antrian (versi development)
- `php artisan pail --timeout=0` — live log viewer (biar error/log kelihatan langsung di terminal)
- `npm run dev` — Vite dev server (auto-reload CSS/JS saat file diubah)

> **Catatan penting:** `composer run dev` **tidak termasuk** `schedule:work` dan `gps:fetch` — kalau butuh data GPS armada ter-update otomatis, tetap perlu jalankan `php artisan schedule:work` di terminal terpisah selain `composer run dev`.

---

## 5. Perintah Tambahan yang Tersedia di Project Ini

Selain command bawaan Laravel, project ini punya beberapa command custom yang berguna:

| Perintah | Fungsi |
|----------|--------|
| `php artisan dlh:setup-seeder` | Setup ulang folder storage + placeholder + seeding data (tanpa reset database, cuma nambah/timpa data seeder) |
| `php artisan dlh:setup-seeder --fresh` | Sama seperti di atas, tapi database di-reset total dulu (`migrate:fresh --seed`) — **hati-hati, semua data lama hilang** |
| `php artisan storage:link` | Membuat symbolic link `public/storage` → `storage/app/public`, wajib supaya file upload (foto, dokumen) bisa diakses lewat browser |
| `php artisan app:db-backup` | Backup database ke file `.sql` secara manual, disimpan di disk privat (pakai PDO murni, tidak butuh tool `mysqldump` di server) |
| `php artisan test:chatbot "pesan"` | Test koneksi chatbot AI (OpenRouter) langsung dari terminal tanpa buka browser, contoh: `php artisan test:chatbot "Halo, jam operasional DLH?"` |
| `php artisan dlh:download-images` | Download gambar dari website DLH Sulteng untuk dipakai di artikel/berita |
| `php artisan pail` | Live log viewer (lihat error/log real-time di terminal, alternatif lebih enak dibaca dibanding `tail -f storage/logs/laravel.log`) |
| `npm run dev` | Vite dev server, auto-compile CSS/JS saat file diubah (dipakai selama development) |
| `npm run build` | Compile & minify CSS/JS untuk production (dipakai sebelum deploy) |

---

## 6. Perintah Testing

Project ini punya 2 jenis testing: PHPUnit (backend) dan Playwright (E2E/browser, sesuai catatan di README bagian struktur direktori `tests/`).

| Perintah | Fungsi |
|----------|--------|
| `composer test` | Jalankan test PHPUnit — otomatis `config:clear` dulu sebelum `php artisan test`, supaya config lama tidak mengganggu hasil test |
| `npm run test:e2e` | Jalankan test end-to-end Playwright (simulasi klik browser sungguhan) |
| `npm run test:e2e:ui` | Sama seperti di atas, tapi buka mode UI interaktif Playwright (bisa lihat step-by-step & debug visual) |
| `npm run test:e2e:report` | Buka laporan HTML hasil test Playwright yang terakhir dijalankan |

---

## 7. Perintah Maintenance & Troubleshooting

Dipakai kalau aplikasi terasa aneh/error setelah update kode, atau sebelum deploy ke production:

| Perintah | Fungsi |
|----------|--------|
| `php artisan config:cache` | Cache semua file config jadi 1 file (mempercepat load), **wajib** dijalankan ulang tiap kali file `.env` atau `config/*.php` diubah di production |
| `php artisan route:cache` | Cache semua route jadi 1 file (mempercepat routing), dipakai di production |
| `php artisan view:cache` | Pre-compile semua file Blade jadi PHP murni, dipakai di production |
| `php artisan config:clear` | Hapus cache config — dipakai kalau perubahan `.env` tidak terbaca (misalnya config sempat di-cache tapi lupa di-clear) |
| `php artisan cache:clear` | Hapus seluruh cache aplikasi (bukan cuma config) |
| `php artisan view:clear` | Hapus cache Blade — dipakai kalau perubahan tampilan tidak muncul-muncul padahal sudah disimpan |
| `php artisan migrate:status` | Cek migrasi mana saja yang sudah/belum jalan — berguna untuk debug kalau ada error "table not found" |
| `php artisan queue:restart` | **Wajib** dijalankan setiap habis deploy/update kode di production yang pakai `queue:work` — supaya worker baca ulang kode terbaru (`queue:work` tidak auto-reload seperti `queue:listen`) |
| `php artisan tinker` | Buka REPL/interactive shell Laravel — berguna untuk cek/ubah data langsung lewat kode tanpa bikin halaman baru (misal `User::where('username','admin')->first()`) |

> **Penting:** `config:cache`/`route:cache`/`view:cache` **hanya untuk production**. Kalau dijalankan pas development, perubahan kode/`.env` bisa jadi tidak kelihatan karena masih baca versi cache lama — kalau ini terjadi, jalankan versi `:clear`-nya masing-masing.

---

## 8. Deploy ke Production (Sesuai Konfigurasi Project)

Project ini sudah dikonfigurasi untuk deploy ke **Railway** (`railway.json`, pakai builder Nixpacks) dan juga punya `Dockerfile` + `docker-compose.yml` + `nginx.conf` untuk deploy manual di VPS. Urutan build & deploy command yang sudah didefinisikan:

| Tahap | Perintah |
|-------|----------|
| Build command | `composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan key:generate && php artisan migrate --force && php artisan storage:link` |
| Start command | `php artisan serve --host=0.0.0.0 --port=$PORT` |

> `--no-dev` artinya package development (Playwright, dll) tidak ikut ter-install di production — lebih ringan. `migrate --force` artinya migrasi dijalankan tanpa prompt konfirmasi interaktif (perlu karena di server tidak ada terminal interaktif untuk jawab "yes").

**Untuk VPS/hosting manual (bukan Railway), tambahan yang wajib disiapkan di server** (dari `panduan-hosting.md`):

1. **Cron job** — karena tidak ada `schedule:work` yang bisa nyala terus di server (beda dengan lokal), scheduler production **wajib** didaftarkan lewat crontab:
   ```
   * * * * * cd /path-ke-proyek && php artisan schedule:run >> /dev/null 2>&1
   ```
   Baris ini dicek Laravel tiap menit, dan dari situ dia yang menentukan tugas terjadwal (`gps:fetch` tiap 30 detik) kapan harus benar-benar dijalankan.

2. **Queue worker sebagai service/daemon** — jangan jalankan `queue:work` manual di server (akan mati kalau terminal ditutup/server restart). Idealnya pakai **Supervisor** (tool Linux buat menjaga proses tetap hidup & auto-restart kalau crash) yang menjalankan `php artisan queue:work --tries=3 --timeout=60` terus-menerus di background. Konfigurasi Supervisor ini **belum ada file contohnya di project** (`Dockerfile` saat ini cuma menjalankan `php-fpm` saja, tanpa queue worker otomatis di dalamnya) — kalau deploy manual, ini perlu disiapkan sendiri di server, atau tanyakan ke saya kalau mau dibuatkan contoh config Supervisor-nya.

---

## 9. Ringkasan: Urutan Kerja Harian Setelah Setup Awal Selesai

Setelah setup awal (bagian 1) sudah dilakukan sekali, untuk kerja sehari-hari cukup:

1. Buka terminal 1 → `composer run dev` (server + queue + log + vite jalan bareng)
2. Buka terminal 2 → `php artisan schedule:work` (biar GPS armada ter-update otomatis)
3. Buka `http://localhost:8000` di browser, login pakai salah satu akun di bagian 2
4. Selesai kerja → `Ctrl+C` di kedua terminal untuk mematikan semuanya