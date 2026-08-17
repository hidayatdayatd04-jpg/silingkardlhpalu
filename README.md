# SILINGKAR — Sistem Informasi Layanan Lingkungan Hidup Kota Palu

> **Dokumentasi teknis untuk pengembang (developer) & staf IT Dinas Lingkungan Hidup (DLH) Kota Palu.**

SILINGKAR adalah aplikasi web terpadu berbasis **Laravel 12** yang menghubungkan **masyarakat** dengan **4 bidang DLH Kota Palu** (Pengendalian, Sampah & LB3, RTH, Tata Penataan) dalam satu portal publik + panel administrasi internal.

Dokumen ini berisi informasi teknis yang dibutuhkan untuk **membangun, menjalankan, mengelola, dan mengembangkan** aplikasi: arsitektur, stack teknologi, instalasi, konfigurasi lingkungan, integrasi eksternal, backup, penjadwalan, hingga troubleshooting.

---

## Daftar Isi

1. [Gambaran Umum & Modul](#1-gambaran-umum--modul)
2. [Stack Teknologi](#2-stack-teknologi)
3. [Arsitektur Aplikasi](#3-arsitektur-aplikasi)
4. [Struktur Direktori](#4-struktur-direktori)
5. [Hak Akses & Role](#5-hak-akses--role)
6. [Sistem Nomor Tiket & Alur Status](#6-sistem-nomor-tiket--alur-status)
7. [Integrasi Layanan Eksternal](#7-integrasi-layanan-eksternal)
8. [Penyimpanan File & Backup](#8-penyimpanan-file--backup)
9. [Prasyarat](#9-prasyarat)
10. [Instalasi & Setup Awal](#10-instalasi--setup-awal)
11. [Menjalankan untuk Pengembangan](#11-menjalankan-untuk-pengembangan)
12. [Menjalankan dengan Docker (Produksi)](#12-menjalankan-dengan-docker-produksi)
13. [Perintah Artisan Tersedia](#13-perintah-artisan-tersedia)
14. [Penjadwalan (Scheduler) & Antrian (Queue)](#14-penjadwalan-scheduler--antrian-queue)
15. [Konfigurasi Lingkungan (.env)](#15-konfigurasi-lingkungan-env)
16. [Akun Admin Bawaan (Seeder)](#16-akun-admin-bawaan-seeder)
17. [Pengembangan & Kontribusi](#17-pengembangan--kontribusi)
18. [Operasional & Troubleshooting](#18-operasional--troubleshooting)
19. [Keamanan](#19-keamanan)
20. [Deployment & Cache Produksi](#20-deployment--cache-produksi)

---

## 1. Gambaran Umum & Modul

Aplikasi terbagi menjadi **dua sisi** yang saling terhubung:

| Sisi | Akses | Fungsi |
|------|-------|--------|
| **Portal Publik** | Tanpa login | Pengaduan, perizinan/rekomendasi, pelacakan status tiket, peta interaktif, monitoring armada GPS, berita, profil dinas, survei IKM, chatbot AI |
| **Panel Admin** (`/admin`) | Login + hak akses per bidang | Kelola pengaduan/permohonan, master data, cetak dokumen PDF, peta GIS, backup, log aktivitas, manajemen pengguna |

Layanan dikelompokkan berdasarkan struktur nyata DLH — **4 bidang** + 1 kelompok konten/sistem:

| Kode Bidang | Bidang | Cakupan |
|-------------|--------|---------|
| `pengendalian` | Pengendalian Dampak Lingkungan | Pengaduan pencemaran, permohonan rekomendasi |
| `sampah-lb3` | Pengelolaan Sampah & LB3 | Pengaduan sampah, registrasi usaha LB3, RINTEK/PERTEK, statistik sampah |
| `tata-penataan` | Tata Penataan Lingkungan | Pengaduan, pelanggaran, sanksi, sosialisasi & monitoring |
| `rth` | Ruang Terbuka Hijau | Pengaduan RTH, penyewaan taman, data tanam pohon |
| `konten` | Konten & Sistem | Artikel/berita, pengguna admin, kesekretariatan |

Penting: **setiap admin bidang hanya dapat mengakses data bidangnya sendiri** (dibatasi lewat `AdminAccess` + `AdminRegistry`).

---

## 2. Stack Teknologi

| Kategori | Teknologi |
|----------|-----------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Frontend** | Blade, Livewire 4, Alpine.js, Tailwind CSS 4 |
| **Build tool** | Vite 7 (ada entry `.ts` untuk halaman tata-lingkungan) |
| **Database** | PostgreSQL (Neon di produksi; `postgres:16` di `docker-compose.yml`) |
| **Role/Permission** | Spatie Laravel Permission |
| **Peta / GIS** | MapLibre GL JS; mendukung impor **Shapefile (SHP)** |
| **PDF** | barryvdh/laravel-dompdf |
| **Export Excel/CSV** | Maatwebsite Excel (didukung fallback ekspor berantre/async) |
| **Gambar** | Intervention Image (kompresi + varian gambar) |
| **Storage cloud** | Backblaze B2 (S3-compatible), Flysystem S3 |
| **Chatbot AI** | Provider OpenAI-compatible (OpenRouter/token router) — API key dikonfigurasi lewat UI admin, bukan `.env` |
| **GPS** | Integrasi `portal.gps.id` |
| **Google Drive** | Google Drive API (folder dokumen publik Tata Lingkungan) |
| **Testing** | PHPUnit (belum ada suite tes — folder `tests/` tidak disertakan) |

---

## 3. Arsitektur Aplikasi

Menggunakan pendekatan **admin panel berbasis registry** — alih-alih controller CRUD terpisah per modul, seluruh CRUD ditangani satu **`ResourceController`** yang membaca definisi dari **`AdminRegistry`**.

```
Portal Publik (Blade + Livewire)      Panel Admin (/admin)
        │                                   │ auth + admin.access + no-store
        └──────────────────────►────────────┘
                                   │
                          ResourceController
                          + AdminRegistry (peta menu & CRUD)
                                   │
                    ┌──────────────┼───────────────┐
                    ▼              ▼               ▼
                 Models        Observers        Services
                 (Eloquent)    (auto tiket       (GPS, AI, Google
                               + notifikasi)     Drive, Statistik,
                                                 Backup, Upload)
                    └──────────────┼───────────────┘
                                   ▼
                              Database
```

**Komponen kunci:**
- **`app/Support/Admin/AdminRegistry.php`** (≈2000 baris) — pusat konfigurasi seluruh menu & CRUD panel admin. Menambah modul baru berarti mendaftarkan resource di sini.
- **`app/Http/Controllers/Admin/ResourceController.php`** — mesin CRUD generik + otorisasi per bidang + ekspor + unggah lampiran + aksi massal.
- **Enums** (`app/Enums/`) — definisi terstruktur status, jenis pengaduan, role, bidang, dsb.
- **Observers** — otomatisasi nomor tiket & notifikasi (aktivitas + admin).
- **Services** (`app/Services/`) — `GpsService`, `AiChatService`, `ChatKnowledgeBase`, `GoogleDriveService`, `StatistikService`, `ShpParserService`, `ImageCompressionService`, `FileUploadService`, `TicketTimelineService`, `MonitoringService`.
- **Support** (`app/Support/`) — `AdminRegistry`, `AdminAccess`, `TicketGenerator`, `DatabaseBackup`, `DataIO`, `PhoneNormalizer`, `NumberFallback`, dll.
- **Policies** (`app/Policies/`) — aturan izin per model.
- **Middleware** — `EnsureAdminPanelAccess`, `NoStoreCacheHeaders`, `TrackWebsiteVisit`, `CheckMaintenanceMode`.

---

## 4. Struktur Direktori

```
DLH - Palu/
├── app/
│   ├── Console/Commands/       # gps:fetch, dlh:* (lihat §13)
│   ├── Enums/                  # Status, jenis, role, bidang
│   ├── Http/Controllers/
│   │   ├── Admin/              # Auth, Dashboard, Resource, Peta, Backup,
│   │   │                       # Setting, Notification, ActivityLog, Help,
│   │   │                       # Profile, Upload, UlasanMasyarakat
│   │   ├── Middleware/         # akses admin, locale, tracking, maintenance
│   │   └── Requests/           # validasi form publik
│   ├── Jobs/                   # GenerateExportJob, ProcessPhotoUpload,
│   │                           # RunBackupJob, RunRestoreJob
│   ├── Livewire/               # ChatBot
│   ├── Models/                 # ~35 model Eloquent
│   ├── Notifications/          # AdminNotification, ExportReady, SanksiJatuhTempo
│   ├── Observers/              # ActivityLogObserver, NotificationObserver
│   ├── Policies/               # aturan izin per model
│   ├── Providers/              # AppServiceProvider, NeonDatabaseProvider
│   ├── Services/               # GPS, AI, Google Drive, Statistik, Backup, dll
│   └── Support/                # AdminRegistry, AdminAccess, DatabaseBackup, dll
├── config/                     # services, monitoring, permission, livewire, dll
├── database/
│   ├── migrations/             # skema (users, laporans, tata penataan, sampah,
│   │                           #   rth, artikel, settings, ai_providers, dsb)
│   └── seeders/                # DatabaseSeeder, RolePermissionSeeder
├── lang/                       # terjemahan (id/)
├── resources/views/
│   ├── admin/                  # dashboard, resources, peta, backup, settings, dll
│   ├── public/                 # seluruh halaman portal publik
│   ├── components/public/      # komponen Blade publik (form, peta, tracking)
│   ├── layouts/                # app.blade.php (publik) & admin.blade.php
│   ├── livewire/               # tampilan chatbot
│   └── pdf/                    # template dokumen PDF
├── routes/
│   ├── web.php                 # seluruh rute publik + admin
│   └── console.php             # penjadwalan scheduler
├── public/                     # entry point & aset publik
├── Dockerfile                  # build image PHP-FPM (multi-stage)
├── docker-compose.yml          # app, nginx, db (PostgreSQL 16), queue, scheduler
└── nginx.conf                  # config nginx (gzip, cache aset, security header)
```


---

## 5. Hak Akses & Role

Login admin hanya melalui `/admin/login` (username **atau** email + password). Hak akses dikendalikan oleh:
- Role Spatie (`app/Enums/AdminRole.php`),
- `AdminAccess` (`app/Support/AdminAccess.php`) — setiap admin bidang hanya melihat menu & data bidangnya,
- mekanisme **hak akses tambahan (`additional_access`)** — Admin dapat memberi admin bidang akses tambahan ke bidang lain tanpa mengubah role utama.

Akun bawaan (dibuat seeder `RolePermissionSeeder`) — lihat [§16](#16-akun-admin-bawaan-seeder).

---

## 6. Sistem Nomor Tiket & Alur Status

Setiap pengajuan publik otomatis mendapat nomor tiket unik (dibuat `TicketGenerator`) dengan awalan sesuai bidang/layanan:

| Awalan | Layanan |
|--------|---------|
| `PDL-XXXX-XXXX` | Pengaduan Pengendalian |
| `SMP-XXXX-XXXX` | Pengaduan/layanan Sampah |
| `RTH-XXXX-XXXX` | Layanan RTH |
| `TTP-XXXX-XXXX` | Pengaduan Tata Penataan |

**Alur:**
1. Masyarakat kirim form → sistem membuat nomor tiket unik.
2. Data masuk ke panel admin bidang terkait.
3. Admin mengubah status (mis. *Diproses* → *Selesai*).
4. Perubahan status memicu **notifikasi admin** (observer) — riwayat tercatat di `activity_logs` & `notifications`, lalu tampil di panel admin. **Tidak ada pengiriman email.**
5. Masyarakat melacak status via halaman "Cek"/`/lacak` menggunakan nomor tiket, dan dapat memberi **umpan balik terhadap tiket** (`ticket_feedbacks`).


---

## 7. Integrasi Layanan Eksternal

Semua kredensial integrasi disimpan di `.env` **kecuali AI chatbot** (lewat UI admin).

### 🚚 GPS Armada (`portal.gps.id`)
- Config: `config/services.php` → `gps`.
- Kelas: `app/Services/GpsService.php`; command `gps:fetch` menyimpan posisi ke `GpsVehicleCache`.
- Data ditampilkan di halaman publik `/armada` dan endpoint `/api/armada-aktif`.
- **Penyegaran otomatis** tiap 30 detik via scheduler (lihat [§14](#14-penjadwalan-scheduler--antrian-queue)).

### 🤖 Chatbot AI
- Endpoint streaming: `POST /api/chatbot/stream` (komponen `app/Livewire/ChatBot.php`).
- Provider AI (OpenRouter/token router — API **OpenAI-compatible**) diatur di **menu Pengaturan Admin** (`/admin/settings`, tabel `ai_providers`), **bukan** di `.env`. Mendukung beberapa provider, API key terenkripsi.
- Memiliki basis pengetahuan lokal (`ChatKnowledgeBase`).

### 📁 Google Drive (dokumen publik Tata Lingkungan)
- Halaman `/tata-lingkungan` menampilkan folder/file Google Drive secara berkategori.
- Config: `config/services.php` → `google_drive`; API key di `.env` (`GOOGLE_DRIVE_API_KEY`), folder dibagikan "siapa pun dengan link".
- Hasil di-cache (default 900 detik).

> ℹ️ **Catatan penting:** aplikasi ini **tidak menggunakan email**. Seluruh notifikasi internal (pengaduan baru, perubahan status, sanksi jatuh tempo, ekspor siap diunduh) dikirim lewat **channel `database`** dan ditampilkan di panel admin (tabel `notifications`). Konfigurasi SMTP/Brevo yang tersisa sudah dihapus dari `.env`.

---

## 8. Penyimpanan File & Backup

- **Upload aplikasi** disimpan di `storage/app/public` (disk `local`), diakses via symlink `storage:link`. Foto otomatis dikompresi + dibuat varian gambar.
- **Cloud storage** — Backblaze B2 (S3-compatible) dapat diaktifkan via `.env` (`B2_*` / `AWS_*`), digunakan sebagai **disk backup (`BACKUP_DISK=b2`)** dan gambar OpenGraph publik via proxy `/file/og`.
- **Backup & restore** — manual dari panel admin (`/admin/backup`): dump database (pg_dump) + seluruh file storage disatukan dalam arsip `.zip` lalu diunggah ke B2. **Membuat backup baru menghapus semua backup lama** — hanya backup terbaru yang tersimpan. Restore bersifat **non-destructive/merge**: data di backup di-upsert, data yang lebih baru atau tidak ada di backup tetap dipertahankan; sebelum restore dibuat **pre-restore backup** sebagai titik rollback jika proses gagal.
- **Pembersihan file yatim** — `dlh:cleanup-orphan-files` menghapus objek B2 tanpa referensi database (dijadwalkan Minggu 03:00); `dlh:cleanup-b2-orphans` membersihkan upload sementara Livewire (`livewire-tmp`).

---

## 9. Prasyarat

- PHP **8.2+** (dengan ekstensi: `pdo_mysql`/`pdo_pgsql`, `gd`, `mbstring`, `zip`, `bcmath`, `intl`, `exif`)
- Composer
- Node.js & NPM
- Database: **PostgreSQL** (default `config/database.php`; produksi memakai Neon, Docker Compose menyediakan `postgres:16`)
- (Opsional) Docker & Docker Compose untuk produksi

> Catatan: koneksi `pgsql` di `.env` produksi memakai **Neon PostgreSQL** (serverless, remote) — `NeonDatabaseProvider` otomatis menyisipkan parameter `options=endpoint=` bila `DB_NEON_ENDPOINT` diisi. Untuk pengembangan lokal cukup kosongkan `DB_NEON_ENDPOINT` (atau pakai service `db` di `docker-compose.yml`).

---

## 10. Instalasi & Setup Awal

```bash
# 1. Install dependensi PHP
composer install

# 2. Siapkan .env + APP_KEY
cp .env.example .env
php artisan key:generate

# 3. Sesuaikan koneksi database di .env (DB_CONNECTION, DB_HOST, DB_DATABASE, ...)
#    lalu jalankan migrasi + seeder dasar
php artisan migrate
php artisan db:seed          # DatabaseSeeder => RolePermissionSeeder (role + akun demo)

# 4. Buat symlink storage (agar file unggahan bisa diakses publik)
php artisan storage:link

# 5. Install & build aset frontend
npm install
npm run build
```

> **Setup lengkap + data contoh** (folder storage, placeholder, seeding master data):
> ```bash
> php artisan dlh:setup-seeder          # tanpa reset
> php artisan dlh:setup-seeder --fresh  # migrate:fresh + seed
> ```

---

## 11. Menjalankan untuk Pengembangan

```bash
# Jalankan server + queue + log (pail) + vite sekaligus:
composer run dev

# Atau manual:
php artisan serve            # web server (http://localhost:8000)
php artisan queue:listen     # pemroses antrian (notifikasi, ekspor, backup)
npm run dev                  # Vite (dev server aset)
```

Akses:
- **Portal publik:** `http://localhost:8000`
- **Panel admin:** `http://localhost:8000/admin/login`
- **Log:** `php artisan pail`


---

## 12. Menjalankan dengan Docker (Produksi)

`docker-compose.yml` mengatur: `app` (PHP-FPM), `nginx` (port 80), `db` (PostgreSQL 16), `queue` (worker), dan `scheduler`.

```bash
# 1. Siapkan .env produksi di root project (dari .env.example yang disanitasi)
cp .env.example .env   # lalu isi kredensial & APP_KEY

# 2. Build & jalankan seluruh service
docker compose up -d --build

# 3. Migrasi + seeder dasar di dalam container app
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

- **Kode + vendor + aset frontend berasal dari image** (Dockerfile multi-stage: base → build → production; PHP 8.2-fpm + Node 18). Yang di-bind dari host hanya `.env`, `storage/`, dan `bootstrap/cache/` — jadi fresh clone di VPS langsung jalan tanpa `composer install`/`npm run build` di host.
- Nginx diatur lewat `nginx.conf` (compression gzip, cache aset 30 hari, security headers, deny akses file tersembunyi) dan membaca kode/aset dari image `app` (`volumes_from`).
- Kredensial DB default ada di `docker-compose.yml` (variabel `DB_DATABASE/DB_USERNAME/DB_PASSWORD`), otomatis di-override oleh `.env` di root.
- Update kode: `git pull && docker compose up -d --build && docker compose exec app php artisan migrate --force`.

### Deployment ke VPS

1. Siapkan `.env` produksi di server (salin dari `.env.example` yang sudah disanitasi — **jangan** menyalin `.env` lokal yang berisi kredensial asli). Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`, lalu generate key di container: `docker compose run --rm app php artisan key:generate`.
2. Isi kredensial database (Neon atau PostgreSQL lokal), B2, GPS, dan Google Drive sesuai kebutuhan.
3. `docker compose up -d --build`, lalu jalankan `migrate --seed` + `storage:link` seperti di atas.
4. **HTTPS**: `nginx.conf` bawaan hanya listen port 80. Pasang reverse proxy dengan TLS otomatis di depannya (Caddy/Traefik/Nginx host + Certbot, atau arahkan lewat Cloudflare), lalu teruskan ke port 80 container.
5. Worker queue & scheduler sudah berjalan sebagai container terpisah — tidak perlu cron tambahan di host.
6. Setelah stabil, jalankan cache produksi (lihat [§20](#20-deployment--cache-produksi)).

---

## 13. Perintah Artisan Tersedia

| Command | Fungsi |
|---------|--------|
| `gps:fetch` | Ambil & cache data posisi armada dari GPS.id |
| `dlh:setup-seeder` | Setup lengkap seeder (folder, placeholder, seeding); `--fresh` untuk reset |
| `dlh:cleanup-orphan-files` | Hapus file B2 yang tidak memiliki referensi di database (`--delete` untuk benar-benar menghapus) |
| `dlh:cleanup-b2-orphans` | Hapus upload sementara Livewire (`livewire-tmp`) di B2 |
| `dlh:download-images` | Download gambar dari website DLH Sulteng untuk artikel |
| `shp:import-bulk` | Impor Shapefile (SHP) secara massal ke tabel GIS |

Jalankan `php artisan list` untuk daftar lengkap.

---

## 14. Penjadwalan (Scheduler) & Antrian (Queue)

### Scheduler (di `routes/console.php`)
| Waktu | Command |
|-------|---------|
| Setiap **30 detik** | `gps:fetch` |
| Minggu **03:00** | `dlh:cleanup-orphan-files --delete` (`onOneServer`) |

### Menjalankan scheduler di server
```bash
# Tambahkan ke crontab server:
* * * * * cd /path-ke-proyek && php artisan schedule:run >> /dev/null 2>&1
```
Bila memakai Docker, kontainer `scheduler` sudah menjalankan `schedule:run` tiap menit.

### Queue worker
Notifikasi, ekspor besar, backup & restore diproses melalui antrian (`QUEUE_CONNECTION=database`). Jalankan worker sebagai proses/layanan:
```bash
php artisan queue:work --tries=3 --timeout=60
```
Dalam Docker, kontainer `queue` sudah otomatis menjalankan worker ini.

> ⚠️ **Wajib** menjalankan `queue:work` dan scheduler — tanpa keduanya, notifikasi/tugas otomatis tidak berjalan dan posisi GPS tidak ter-update.

---

## 15. Konfigurasi Lingkungan (.env)

Salinan lengkap ada di `.env.example`. Poin penting:

```env
# --- Aplikasi ---
APP_NAME="DLH Kota Palu"
APP_ENV=local / production
APP_DEBUG=true / false
APP_URL=https://silingkardlhpalu
APP_LOCALE=id

# --- Database ---
# PostgreSQL biasa (dev lokal / docker compose):
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dlh_palu
DB_USERNAME=dlh_palu
DB_PASSWORD=
DB_SSLMODE=prefer
# Neon (produksi): isi DB_HOST lengkap + DB_NEON_ENDPOINT, DB_SSLMODE=require
# DB_NEON_ENDPOINT=ep-xxx-yyy

# --- Queue & Cache ---
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=1900   # > durasi job terlama (backup/restore ~30 mnt)
CACHE_STORE=file            # ganti redis untuk produksi
SESSION_DRIVER=database|file

# --- Storage cloud (Backblaze B2, untuk backup + OG image) ---
B2_KEY_ID=
B2_APPLICATION_KEY=
B2_BUCKET=
B2_REGION=us-west-004
B2_ENDPOINT=https://s3.us-west-004.backblazeb2.com
# Lihat juga AWS_* di atasnya
BACKUP_DISK=b2

# --- GPS Armada ---
GPS_LOGIN_URL=https://portal.gps.id/backend/api/single_login
GPS_MONITORING_URL=https://portal.gps.id/backend/seen/gps/list_monitoring
GPS_USERNAME=
GPS_PASSWORD=
GPS_PALU_DEFAULT_LAT=-0.9
GPS_PALU_DEFAULT_LNG=119.87

# --- Google Drive (halaman tata-lingkungan) ---
GOOGLE_DRIVE_API_KEY=
GOOGLE_DRIVE_TATA_LINGKUNGAN_FOLDER_ID=
GOOGLE_DRIVE_CACHE_TTL=900
GOOGLE_DRIVE_MAX_DEPTH=8
```

> **AI chatbot**: API key & model **tidak** di `.env` — dikelola lewat menu **Pengaturan Admin** (`/admin/settings` → tabel `ai_providers`).
> **Monitoring infrastruktur** (B2 & Neon): kuota & status plan dapat diatur lewat `config/monitoring.php` (`B2_STORAGE_LIMIT_GB`, `NEON_STORAGE_LIMIT_GB`, `B2_PLAN`, `NEON_PLAN`).

---

## 16. Akun Admin Bawaan (Seeder)

Dibuat oleh `RolePermissionSeeder`. **Semua password demo wajib diganti di produksi.**

| Nama | Username | Password | Role / Akses |
|------|----------|----------|--------------|
| Admin | `admin` | `admin123` | Admin — akses penuh semua bidang |
| Admin Pengendalian | `pengendalian` | `pengendalian123` | Bidang Pengendalian |
| Admin Sampah & LB3 | `sampah-lb3` | `sampah123` | Bidang Sampah & LB3 |
| Admin Tata Penataan | `tata-penataan` | `tata123` | Bidang Tata Penataan |
| Admin RTH | `rth` | `rth123` | Bidang RTH |

Admin dapat mengelola pengguna (`/admin/user/...`) dan melakukan **reset password** (route `{resource}/reset-password`) jika admin bidang lupa password.


---

## 17. Pengembangan & Kontribusi

**Alur kerja standar (git):** branch `main` aktif; gunakan commit deskriptif berprefiks jenis perubahan (lihat sejarah: `perf(admin):`, `perf(db):`, `feat(...):`, `fix(...):`).

**Menambahkan modul baru di panel admin** umumnya cukup dengan:
1. Membuat model + migrasi.
2. Mendaftarkan resource di `AdminRegistry::all()` (label, model, kolom tabel), definisi lengkapnya di `app/Support/Admin/AdminRegistry.php`.
3. (Opsional) tambahkan kolom/field form di definisi resource; `ResourceController` menangani CRUD, ekspor, dan unggahan secara generik.
4. Menetapkan izin/policy bila perlu.

**Penulisan bersih:** gunakan `laravel/pint` untuk format kode (`vendor/bin/pint`).

**Frontend:** aset di-render melalui Vite — entry terdaftar di `vite.config.js` (mis. `map-bundle.js` untuk halaman ber-peta, `dashboard-charts.js` untuk grafik, `tata-lingkungan.ts` untuk TS). Jalankan `npm run build` sebelum deployment.

**TypeScript check:** `npm run typecheck`.

---

## 18. Operasional & Troubleshooting

### Update & deployment
1. `git pull`
2. `composer install --no-dev --optimize-autoloader` (tanpa `--no-dev` bila perlu tool dev)
3. `php artisan migrate --force`
4. `npm ci && npm run build`
5. Bersihkan cache: lihat [§20](#20-deployment--cache-produksi)

### Masalah umum
| Gejala | Solusi |
|--------|--------|
| File unggahan tidak tampil | jalankan `php artisan storage:link` |
| Notifikasi tidak muncul | pastikan `php artisan queue:work` berjalan; notifikasi tersimpan di database & tampil di panel admin |
| Posisi armada GPS tidak ter-update | pastikan scheduler berjalan (`schedule:run` tiap menit) & kredensial `GPS_*` benar |
| Error foreign key saat seed | jalankan `php artisan migrate:fresh --seed` |
| Error `Class not found` | `composer dump-autoload` |
| Performa lambat / banyak query | pastikan `CACHE_STORE` bukan database; gunakan Redis untuk produksi |
| Chatbot tidak menjawab | cek provider AI di menu Pengaturan Admin (API key valid, model tersedia) |

### Log
- Log aplikasi: `storage/logs/laravel.log`.
- Real-time log di dev: `php artisan pail`.

---

## 19. Keamanan

- **Autentikasi & otorisasi berlapis**: middleware (`auth`, `admin.access`), otorisasi di controller, serta Spatie policies per model.
- **Password ter-hash** (BCrypt rounds 12) + proteksi agar role Admin tidak dapat diturunkan sembarangan.
- **Rate limiting** (`throttle`) pada halaman cek status, unduh PDF, login, input form publik, dan endpoint chatbot/API.
- **Header keamanan** via `nginx.conf` (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`); header `no-store` pada grup route admin.
- **API key AI** terenkripsi di database.
- **Log aktivitas** (`activity_logs`) & **notifikasi admin** untuk audit.
- Sebelum produksi: **ganti password demo** (§16), set `APP_ENV=production` & `APP_DEBUG=false`, aktifkan HTTPS.

---

## 20. Deployment & Cache Produksi

Setelah deploy, jalankan cache Laravel agar performa optimal:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> Gunakan `php artisan config:clear`/`optimize:clear` saat mengubah `.env` di lingkungan produksi. Untuk update aset frontend selalu jalankan `npm run build` setelahnya agar aset versi baru ter-generate.

---

## Referensi & Peta Rute Utama (Publik)

| Halaman | URL |
|---------|-----|
| Beranda / lacak tiket | `/`, `/lacak` |
| Pengaduan (semua bidang) | `/pengaduan-pengendalian`, `/pengaduan-sampah`, `/pengaduan-rth`, `/pengaduan-tata-penataan` |
| Permohonan rekomendasi | `/permohonan-rekomendasi` |
| RINTEK/PERTEK | `/pengajuan-rintek-pertek` |
| Registrasi usaha LB3 | `/registrasi-usaha-lb3` |
| Pinjam/penyewaan taman | `/pinjam-taman` |
| Peta persampahan / objek pengawasan | `/peta-persampahan`, `/peta-objek-pengawasan` |
| Monitoring armada (GPS) | `/armada` |
| Dokumen Tata Lingkungan (Google Drive) | `/tata-lingkungan` |
| Berita / profil / tentang | `/berita`, `/profil`, `/tentang` |
| Chatbot AI | komponen pada portal (`POST /api/chatbot/stream`) |
| Endpoint armada aktif (JSON) | `/api/armada-aktif` |

---

<p align="center">
  <strong>Dinas Lingkungan Hidup Kota Palu</strong><br>
  <em>Melayani dengan Digital, Menjaga Lingkungan untuk Generasi Mendatang</em>
</p>

