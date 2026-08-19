# SILINGKAR — Sistem Informasi Layanan Lingkungan Hidup Kota Palu

SILINGKAR adalah portal layanan DLH Kota Palu. Aplikasi ini menyediakan layanan publik, pelacakan tiket, informasi dinas, dan panel administrasi internal berbasis peran.

## Fitur utama

- Layanan publik untuk pengaduan Pengendalian, Sampah, RTH, dan Tata Penataan; permohonan rekomendasi, pinjam taman, registrasi usaha LB3, serta pengajuan RINTEK/PERTEK.
- Pelacakan satu pintu di `/lacak`, lengkap dengan riwayat status dan umpan balik masyarakat.
- Berita, profil, dokumen Tata Lingkungan dari Google Drive, peta persampahan, dan monitoring armada GPS.
- Panel `/admin` dengan CRUD berbasis registry, pembatasan akses per bidang, ekspor CSV/XLSX, PDF, peta GIS/Shapefile, audit log, notifikasi internal, backup/restore, dan monitoring Neon/B2.
- Chatbot AI streaming dengan provider OpenAI-compatible yang dikelola dari Pengaturan Admin. Kunci provider disimpan terenkripsi di database.
- reCAPTCHA v2 Invisible pada form publik, throttling endpoint, security headers, trusted proxy, dan endpoint `/.well-known/security.txt`.

## Teknologi

| Area           | Teknologi                                                      |
| -------------- | -------------------------------------------------------------- |
| Backend        | PHP 8.2+, Laravel 12                                           |
| UI             | Blade, Livewire 4, Alpine.js, Tailwind CSS 4                   |
| Build          | Vite 7, TypeScript                                             |
| Database       | PostgreSQL; Neon untuk produksi atau PostgreSQL 16 di Docker   |
| Storage/backup | Local storage dan Backblaze B2 (S3-compatible)                 |
| Peta           | MapLibre GL, impor Shapefile                                   |
| Integrasi      | portal.gps.id, Google Drive API, provider AI OpenAI-compatible |
| Dokumen        | DomPDF, ekspor CSV/XLSX tanpa dependensi spreadsheet           |

## Arsitektur singkat

Portal publik dan panel admin menggunakan route Blade/Livewire. Sebagian besar CRUD admin ditangani oleh `ResourceController` yang membaca definisi resource dari `app/Support/Admin/AdminRegistry.php`. Model, policy, observer, dan service memisahkan aturan bisnis, otorisasi, nomor tiket, notifikasi, unggahan, GIS, GPS, AI, statistik, serta backup.

Direktori penting:

```text
app/
  Console/Commands/       # GPS, seeder, impor SHP, pembersihan B2
  Http/Controllers/       # endpoint publik dan admin
  Livewire/               # chatbot
  Services/               # AI, GPS, Drive, upload, statistik, GIS
  Support/Admin/          # registry dan akses admin
database/migrations/      # skema PostgreSQL
resources/views/          # view portal, admin, PDF, dan komponen
routes/web.php            # rute HTTP
routes/console.php        # scheduler
```

## Prasyarat

- PHP 8.2+ dengan `pdo_pgsql`, `gd`, `mbstring`, `zip`, `bcmath`, `intl`, dan `exif`.
- Composer, Node.js, dan npm.
- PostgreSQL atau akun Neon.
- Opsional: Docker Desktop/Compose, Backblaze B2, Google Drive API, dan akun GPS.id.

## Setup lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Atur koneksi PostgreSQL pada `.env`, lalu buat skema dan aset:

```bash
php artisan migrate
php artisan storage:link
npm install
npm run build
```

Untuk data role dan akun contoh, jalankan seeder secara eksplisit:

```bash
php artisan db:seed
# atau setup lengkap
php artisan dlh:setup-seeder
```

Menjalankan pengembangan:

```bash
composer run dev
```

Perintah tersebut menjalankan web server, queue listener, Pail, dan Vite. Alternatifnya, jalankan `php artisan serve`, `php artisan queue:work`, dan `npm run dev` secara terpisah, `php artisan queue:restart` Untuk Restart queue listener.

## Docker dan produksi

`docker-compose.yml` menyediakan `app`, `nginx`, `db` (PostgreSQL 16), `queue`, dan `scheduler`.

```bash
copy .env.example .env
# isi APP_KEY serta konfigurasi produksi
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
```

Untuk produksi, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` ke URL HTTPS, `SESSION_SECURE_COOKIE=true`, dan isi `TRUSTED_PROXIES` hanya dengan alamat reverse proxy yang benar. Nginx bawaan melayani HTTP; TLS harus ditangani reverse proxy di depannya (misalnya Caddy, Cloudflare, atau Nginx host).

Setelah deployment:

```bash
php artisan optimize
php artisan queue:restart
```

## Konfigurasi lingkungan

Mulai dari `.env.example`; jangan commit `.env`. Bagian inti:

```dotenv
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost
TRUSTED_PROXIES=172.16.0.0/12,127.0.0.1

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dlh_palu
DB_USERNAME=dlh_palu
DB_PASSWORD=
DB_SSLMODE=prefer
# DB_NEON_ENDPOINT=ep-xxx-yyy   # wajib diisi untuk Neon

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

B2_KEY_ID=
B2_APPLICATION_KEY=
B2_BUCKET=
B2_REGION=us-west-004
B2_ENDPOINT=https://s3.us-west-004.backblazeb2.com
BACKUP_DISK=b2

RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
```

Untuk Neon, gunakan host endpoint Neon, `DB_SSLMODE=require`, dan isi `DB_NEON_ENDPOINT`. `NeonDatabaseProvider` akan menambahkan parameter endpoint ke koneksi PostgreSQL.

Disk utama aplikasi tetap `local`; B2 digunakan untuk backup dan objek cloud terkait. Jangan mengubah `FILESYSTEM_DISK` ke B2 tanpa memahami konsekuensi upload sementara Livewire. `B2_STORAGE_LIMIT_GB`, `NEON_STORAGE_LIMIT_GB`, `B2_PLAN`, dan `NEON_PLAN` mengatur informasi monitoring pada admin.

Konfigurasi tambahan tersedia di `.env.example`:

- `GPS_*` untuk data armada dari portal.gps.id.
- `GOOGLE_DRIVE_*` untuk dokumen Tata Lingkungan.
- `RECAPTCHA_*` untuk verifikasi form publik.

Provider chatbot tidak memakai variabel `.env`: kelola melalui **Admin → Pengaturan**. API key diproteksi oleh enkripsi Laravel; pastikan `APP_KEY` tidak berubah setelah provider dibuat.

## Operasional data dan storage

Backup dibuat dari **Admin → Backup Database**. Arsip berisi dump PostgreSQL dan file storage, lalu disimpan pada disk `BACKUP_DISK` (biasanya B2). Pembuatan backup baru mempertahankan hanya backup terbaru. Restore bersifat merge/non-destruktif dan membuat backup sebelum pemulihan.

Perintah yang tersedia:

| Perintah                            | Fungsi                                                        |
| ----------------------------------- | ------------------------------------------------------------- |
| `gps:fetch`                         | Mengambil posisi armada dan menyimpannya ke cache database.   |
| `dlh:setup-seeder [--fresh]`        | Menyiapkan data contoh; `--fresh` mereset skema lebih dahulu. |
| `dlh:cleanup-orphan-files --delete` | Menghapus objek B2 yang tidak lagi direferensikan database.   |
| `dlh:cleanup-b2-orphans [--all]`    | Membersihkan upload sementara Livewire pada B2.               |
| `dlh:download-images`               | Mengunduh gambar artikel sumber.                              |
| `shp:import-bulk`                   | Mengimpor file Shapefile secara massal ke layer GIS.          |

Scheduler menjalankan `gps:fetch` setiap 30 detik dan `dlh:cleanup-orphan-files --delete` setiap Minggu pukul 03:00. Pada server tanpa container scheduler, pasang cron berikut:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Queue memakai driver database secara default. Jalankan worker terus-menerus di produksi:

```bash
php artisan queue:work --tries=1 --timeout=0
```

### Reset total lingkungan

`php artisan migrate:fresh` menghapus seluruh tabel lalu membuat ulang **skema kosong** dari migration. Ini tidak menghapus objek Backblaze B2. Sebelum reset produksi, buat dan unduh backup jika data masih dibutuhkan.

Untuk mengosongkan B2, hapus seluruh object version dan delete marker pada bucket melalui kredensial B2 yang tepat. Perintah pembersih bawaan hanya menghapus file yatim atau upload sementara, bukan seluruh bucket. Operasi ini permanen dan harus dibatasi ke bucket aplikasi.

Setelah reset tanpa seeder, tidak ada akun admin, role, konfigurasi provider AI, atau konten. Jalankan `php artisan db:seed` hanya jika memang ingin menambahkan data awal/demo.

## Rute penting

| Area              | Rute                                                                                                       |
| ----------------- | ---------------------------------------------------------------------------------------------------------- |
| Beranda           | `/`                                                                                                        |
| Layanan/pengaduan | `/pengaduan`, `/pengaduan-pengendalian`, `/pengaduan-sampah`, `/pengaduan-rth`, `/pengaduan-tata-penataan` |
| Pelacakan         | `/lacak`                                                                                                   |
| Armada            | `/armada`, `/api/armada-aktif`                                                                             |
| Tata Lingkungan   | `/tata-lingkungan`                                                                                         |
| Berita            | `/berita` dan `/berita/{slug}`                                                                             |
| Login/panel       | `/admin/login`, `/admin`                                                                                   |
| Chatbot stream    | `POST /api/chatbot/stream`                                                                                 |

## Keamanan dan troubleshooting

- Jangan aktifkan `APP_DEBUG` di produksi atau menyimpan kredensial pada repository.
- Batasi `TRUSTED_PROXIES`; jangan gunakan `*` di produksi.
- Semua endpoint publik penting diberi throttle; jangan hapus middleware ini tanpa mitigasi pengganti.
- Pastikan folder Google Drive publik memang dibagikan untuk dibaca dan API Google Drive aktif.
- Bila unggahan tidak bisa diakses lokal, jalankan kembali `php artisan storage:link` dan cek permission `storage/` serta `bootstrap/cache/`.
- Bila job tertahan, periksa `jobs`/`failed_job`, jalankan worker, kemudian `php artisan queue:restart` setelah deploy.
- Lihat log aplikasi dengan `php artisan pail` atau `storage/logs/laravel.log`.

## Pengembangan

```bash
npm run typecheck
npm run build
php artisan optimize:clear
```

Jaga migration bersifat aman untuk database yang sudah ada, gunakan policy dan `AdminAccess` untuk resource baru, serta perbarui registry ketika menambahkan modul CRUD admin.
