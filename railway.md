# Panduan Deploy SILINGKAR ke Railway

Panduan lengkap step-by-step untuk meng-hosting aplikasi **SILINGKAR** (Sistem Informasi Layanan Lingkungan Hidup Kota Palu) ke platform **Railway**.

---

## Daftar Isi

1. [Ringkasan](#1-ringkasan)
2. [Persyaratan](#2-persyaratan)
3. [Persiapan Sebelum Deploy](#3-persiapan-sebelum-deploy)
4. [Deploy Langsung ke Railway](#4-deploy-langsung-ke-railway)
5. [Konfigurasi Service Tambahan](#5-konfigurasi-service-tambahan)
6. [Konfigurasi Environment Variables Lengkap](#6-konfigurasi-environment-variables-lengkap)
7. [Generate APP_KEY & Migrasi Database](#7-generate-app_key--migrasi-database)
8. [Verifikasi Deployment](#8-verifikasi-deployment)
9. [Custom Domain](#9-custom-domain)
10. [Maintenance & Update](#10-maintenance--update)
11. [Troubleshooting](#11-troubleshooting)
12. [Arsitektur di Railway](#12-arsitektur-di-railway)

---

## 1. Ringkasan

| Item | Detail |
|------|--------|
| Platform | Railway (PaaS) |
| Biaya | Free tier (500 jam/bulan) atau mulai $5/bulan |
| Builder | Nixpacks (otomatis deteksi Laravel) |
| Stack | Laravel 12 + PHP 8.2 + MySQL 8.0 + Node.js 18 |
| File konfigurasi | `railway.json` (sudah ada di root project) |

**Kelebihan Railway untuk project ini:**
- Tidak perlu setup server manual
- MySQL database disediakan langsung
- Auto-deploy saat push ke GitHub
- SSL/HTTPS otomatis
- Free tier untuk testing/demo

**Kekurangan:**
- Free tier punya limit 500 jam/bulan (sleep saat idle)
- Queue worker & scheduler perlu service terpisah (berbayar jika terus nyala)
- Tidak ada akses SSH penuh ke server

---

## 2. Persyaratan

### Persyaratan Sistem

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Node.js | 18+ (untuk build asset) |
| Composer | 2.x |

### Ekstensi PHP yang Diperlukan

```
mbstring, xml, curl, gd, pdo_mysql, zip, bcmath, fileinfo, tokenizer, dom, intl, opcache
```

> Semua ekstensi ini sudah termasuk dalam Nixpacks builder Railway secara default.

---

## 3. Persiapan Sebelum Deploy

### 3.1 Push ke GitHub

Project harus sudah di-push ke repository GitHub:

```bash
git init
git add .
git commit -m "Initial commit for Railway deploy"
git remote add origin https://github.com/USERNAME/dlh-palu.git
git branch -M main
git push -u origin main
```

**Pastikan:**
- [ ] File `.env.example` sudah di-commit (sudah ada)
- [ ] File `.env` TIDAK di-commit (sudah ada di `.gitignore`)
- [ ] File `railway.json` sudah ada di root project (sudah ada)

### 3.2 Buat Akun Layanan Eksternal

Buat akun di layanan berikut yang dibutuhkan aplikasi:

| Layanan | Fungsi | Link Daftar |
|---------|--------|-------------|
| Railway | Hosting utama | https://railway.app |
| Brevo | Email/SMTP (300 email/hari gratis) | https://app.brevo.com/account/register/ |
| OpenRouter | AI Chatbot (model gratis tersedia) | https://openrouter.ai/ |

### 3.3 Catatan Kredensial

Siapkan catatan kredensial berikut sebelum mulai:

| Kredensial | Sumber |
|------------|--------|
| GitHub Token / SSH Key | GitHub Settings |
| Brevo SMTP Login | Brevo Dashboard |
| Brevo SMTP Key | Brevo Dashboard → SMTP |
| OpenRouter API Key | https://openrouter.ai/keys |
| GPS API (opsional) | Portal GPS masing-masing |

---

## 4. Deploy Langsung ke Railway

### Langkah 1: Buat Akun Railway

1. Buka https://railway.app
2. Klik **Login** → pilih **Login with GitHub**
3. Izinkan akses ke GitHub repositories

### Langkah 2: Buat Project Baru

1. Klik tombol **New Project** (icon `+`)
2. Pilih **Deploy from GitHub Repo**
3. Cari dan pilih repository `dlh-palu`
4. Railway akan otomatis mendeteksi ini sebagai project Laravel

### Langkah 3: Tambah MySQL Database

1. Di dashboard project, klik **New** → **Database** → **MySQL**
2. Tunggu MySQL instance selesai dibuat (~1-2 menit)
3. Klik MySQL instance → tab **Variables**
4. Salin variabel connection berikut untuk digunakan nanti:
   - `MYSQL_HOST`
   - `MYSQL_PORT`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`

> Variabel-variabel ini akan di-reference di environment variables Laravel.

### Langkah 4: Konfigurasi Environment Variables

1. Klik service Laravel yang sudah di-deploy
2. Tab **Variables** → klik **Raw Editor**
3. Paste seluruh konfigurasi berikut:

```env
# ===========================
# APP SETTINGS
# ===========================
APP_NAME="SILINGKAR DLH Kota Palu"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://namaproject.up.railway.app

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

# ===========================
# DATABASE (MySQL dari Railway)
# ===========================
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQL_DATABASE}
DB_USERNAME=${MYSQL_USER}
DB_PASSWORD=${MYSQL_PASSWORD}

# ===========================
# SESSION & CACHE (database)
# ===========================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=

CACHE_STORE=database
QUEUE_CONNECTION=database

# ===========================
# LOGGING
# ===========================
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# ===========================
# MAIL (Brevo SMTP)
# ===========================
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-login-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-verified-email@gmail.com"
MAIL_FROM_NAME="DLH Kota Palu"

# ===========================
# GPS API (opsional)
# ===========================
GPS_LOGIN_URL=
GPS_MONITORING_URL=
GPS_USERNAME=
GPS_PASSWORD=
GPS_PALU_DEFAULT_LAT=-0.9
GPS_PALU_DEFAULT_LNG=119.87

# ===========================
# OPENROUTER AI CHATBOT
# ===========================
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENROUTER_MODEL=tencent/hy3:free

# ===========================
# FILESYSTEM
# ===========================
FILESYSTEM_DISK=local
```

**Penting:** Ganti nilai `APP_URL` dengan URL yang benar setelah deploy (lihat di tab **Settings** → **Networking**).

### Langkah 5: Build & Start Commands

Railway sudah otomatis mendeteksi dari file `railway.json`:

**Build Command** (otomatis dari `railway.json`):
```bash
composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan key:generate && php artisan migrate --force && php artisan storage:link
```

**Start Command** (otomatis dari `railway.json`):
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

**Healthcheck Path:** `/`

Jika perlu manual override, buka tab **Settings** di service Laravel dan atur:
- **Build Command** → paste build command di atas
- **Start Command** → paste start command di atas
- **Healthcheck Path** → `/`

> `--no-dev` memastikan package development (Playwright, dll) tidak ikut ter-install di production. `migrate --force` menjalankan migrasi tanpa prompt konfirmasi interaktif.

### Langkah 6: Tunggu Build & Deploy

1. Railway akan otomatis menjalankan build command
2. Proses build biasanya memakan waktu 3-7 menit pertama kali
3. Monitor progress di tab **Deployments**
4. Setelah selesai, klik URL yang tersedia (contoh: `https://namaproject.up.railway.app`)

---

## 5. Konfigurasi Service Tambahan

Agar semua fitur berfungsi di production, ada 2 service tambahan yang perlu dibuat: **Queue Worker** dan **Scheduler**.

### 5.1 Queue Worker (untuk Email Notifikasi)

Queue worker memproses job seperti `SendEmailNotificationJob` secara async/background.

**Cara membuat:**

1. Di dashboard project, klik **New** → **Empty Service**
2. Namai service ini **worker**
3. Buka tab **Settings**:
   - **Start Command**: `php artisan queue:work --tries=3 --timeout=60`
   - **Docker Image** atau **Service Source**: gunakan source yang sama dengan app utama

**Atau pakai Procfile:**

Buat file `Procfile` di root project:
```
web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --tries=3 --timeout=60
```

### 5.2 Scheduler (untuk GPS Fetch Otomatis)

Scheduler menjalankan `gps:fetch` otomatis setiap 30 detik sesuai jadwal di `routes/console.php`.

**Cara membuat:**

1. Di dashboard project, klik **New** → **Empty Service**
2. Namai service ini **scheduler**
3. Buka tab **Settings**:
   - **Start Command**: `php artisan schedule:work`

### 5.3 Ringkasan Service di Railway

| Service | Start Command | Fungsi | Biaya |
|---------|---------------|--------|-------|
| **app** | `php artisan serve --host=0.0.0.0 --port=$PORT` | Web server utama | Wajib |
| **mysql** | (managed) | Database | Wajib |
| **worker** | `php artisan queue:work --tries=3 --timeout=60` | Email notifikasi async | Opsional |
| **scheduler** | `php artisan schedule:work` | GPS fetch otomatis | Opsional |

> **Catatan:** Worker dan scheduler hanya dibutuhkan jika fitur email notifikasi dan GPS tracking aktif. Untuk demo/testing, cukup app + mysql saja.

---

## 6. Konfigurasi Environment Variables Lengkap

Berikut penjelasan setiap variabel yang perlu diisi:

### 6.1 Variabel Wajib

| Variabel | Fungsi | Cara Mendapat |
|----------|--------|---------------|
| `APP_KEY` | Enkripsi session/cookie | Auto-generate saat build |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi MySQL | Dari Railway MySQL variables |
| `APP_URL` | URL aplikasi | Dari Railway deployment URL |

### 6.2 Variabel Fitur Email

| Variabel | Fungsi | Isian |
|----------|--------|-------|
| `MAIL_HOST` | Server SMTP | `smtp-relay.brevo.com` |
| `MAIL_PORT` | Port SMTP | `587` |
| `MAIL_USERNAME` | Username SMTP | Login email Brevo |
| `MAIL_PASSWORD` | Password SMTP | SMTP Key dari Brevo |
| `MAIL_FROM_ADDRESS` | Alamat pengirim | Email verified di Brevo |

### 6.3 Variabel GPS (Opsional)

| Variabel | Fungsi |
|----------|--------|
| `GPS_LOGIN_URL` | URL login API GPS.id |
| `GPS_MONITORING_URL` | URL monitoring GPS.id |
| `GPS_USERNAME` | Username GPS.id |
| `GPS_PASSWORD` | Password GPS.id |
| `GPS_PALU_DEFAULT_LAT` | Latitude default Palu (`-0.9`) |
| `GPS_PALU_DEFAULT_LNG` | Longitude default Palu (`119.87`) |

### 6.4 Variabel AI Chatbot

| Variabel | Fungsi | Isian |
|----------|--------|-------|
| `OPENROUTER_API_KEY` | API key OpenRouter | Dari https://openrouter.ai/keys |
| `OPENROUTER_MODEL` | Model AI yang dipakai | `tencent/hy3:free` (gratis) |

---

## 7. Generate APP_KEY & Migrasi Database

Setelah environment variables terkonfigurasi, jalankan perintah berikut menggunakan **Railway CLI**:

### Install Railway CLI

```bash
npm install -g @railway/cli
```

### Login & Link Project

```bash
# Login ke Railway
railway login

# Pilih project
railway link
```

### Jalankan Perintah

```bash
# Generate APP_KEY
railway run php artisan key:generate

# Jalankan migrasi database
railway run php artisan migrate --force

# Buat storage link (agar upload foto bisa diakses)
railway run php artisan storage:link

# Seed data default (opsional - buat akun admin default)
railway run php artisan db:seed
```

### Akun Default Setelah Seed

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin (akses penuh) |
| `pengendalian` | `pengendalian123` | Admin Pengendalian |
| `sampah-lb3` | `sampah123` | Admin Sampah & LB3 |
| `tata-penataan` | `tata123` | Admin Tata Penataan |
| `rth` | `rth123` | Admin RTH |

> Ganti password ini sebelum project dipakai serius!

---

## 8. Verifikasi Deployment

### 8.1 Cek Halaman Publik

1. Buka URL: `https://namaproject.up.railway.app`
2. Pastikan halaman utama tampil dengan benar
3. Cek navigasi dan link berfungsi

### 8.2 Cek Login Admin

1. Buka: `https://namaproject.up.railway.app/admin`
2. Login dengan salah satu akun default
3. Pastikan dashboard admin tampil

### 8.3 Cek Fitur

- [ ] Form pengaduan masyarakat bisa diisi dan dikirim
- [ ] Upload foto laporan berhasil
- [ ] Email notifikasi terkirim (cek Brevo dashboard)
- [ ] Chatbot AI merespons
- [ ] GPS tracking berfungsi (jika dikonfigurasi)
- [ ] Export CSV berfungsi
- [ ] PDF laporan bisa di-download
- [ ] Statistik dashboard tampil dengan benar

### 8.4 Cek Security

- [ ] `APP_DEBUG=false` (sudah di-set)
- [ ] `APP_KEY` sudah di-generate
- [ ] `.env` tidak bisa diakses dari browser
- [ ] HTTPS aktif (otomatis dari Railway)

---

## 9. Custom Domain

### Langkah 1: Tambah Custom Domain

1. Di Railway dashboard → klik service Laravel
2. Tab **Settings** → **Networking**
3. Klik **Custom Domain**
4. Masukkan domain Anda (contoh: `dlh-palu.go.id`)
5. Catat DNS records yang diberikan Railway

### Langkah 2: Konfigurasi DNS

Di panel DNS registrar domain Anda, tambahkan record:

| Type | Name | Value |
|------|------|-------|
| CNAME | `@` atau subdomain | `namaproject.up.railway.app` |

### Langkah 3: Update APP_URL

Setelah DNS ter-propagasi, update variabel:
```
APP_URL=https://dlh-palu.go.id
```

### Langkah 4: Tunggu Propagasi

- Propagasi DNS: 5 menit - 48 jam
- SSL/HTTPS: otomatis diberikan Railway (Let's Encrypt)

---

## 10. Maintenance & Update

### Update Kode

Setelah push ke GitHub, Railway akan otomatis rebuild dan deploy:

```bash
git add .
git commit -m "Update fitur X"
git push origin main
```

### Manual Update via CLI

```bash
# Jalankan migrasi jika ada perubahan database
railway run php artisan migrate --force

# Clear cache
railway run php artisan config:clear
railway run php artisan cache:clear

# Seed data tambahan (opsional)
railway run php artisan db:seed
```

### Lihat Log

```bash
# Via CLI
railway logs

# Atau di dashboard → tab Logs
```

### Backup Database

```bash
# Via Railway CLI
railway run php artisan app:db-backup

# Atau export manual dari Railway MySQL dashboard
```

---

## 11. Troubleshooting

### Error 500 Internal Server Error

```bash
# Cek log
railway logs

# Clear semua cache
railway run php artisan config:clear
railway run php artisan cache:clear
railway run php artisan view:clear

# Cek status migrasi
railway run php artisan migrate:status
```

### Error "No Application Encryption Key"

```bash
railway run php artisan key:generate
```

### Error Database Connection

```bash
# Pastikan variabel MySQL sudah benar di environment variables
# Cek di Railway dashboard → MySQL service → Variables

# Test koneksi
railway run php artisan migrate:status
```

### Email Tidak Terkirim

1. Cek `MAIL_USERNAME` dan `MAIL_PASSWORD` di environment variables
2. Pastikan email sudah verified di Brevo
3. Cek log email di Brevo dashboard

### Chatbot AI Tidak Merespons

1. Cek `OPENROUTER_API_KEY` sudah benar
2. Test koneksi: `railway run php artisan test:chatbot "Halo"`
3. Pastikan kuota OpenRouter masih ada

### GPS Tracking Tidak Update

1. Pastikan service **scheduler** sudah running
2. Cek `GPS_USERNAME` dan `GPS_PASSWORD`
3. Test manual: `railway run php artisan gps:fetch`

### Build Gagal

```bash
# Cek log build di Railway dashboard → tab Deployments
# Common issues:
# - Memory limit: Railway free tier punya limit
# - npm install timeout: coba push lagi
# - Missing dependency: pastikan package.json dan composer.json lengkap
```

### Aplikasi Sleep (Free Tier)

Railway free tier akan sleep setelah idle beberapa menit. Solusi:
1. Upgrade ke paid plan ($5/bulan) agar tidak sleep
2. Atau gunakan UptimeRobot untuk keep-alive:
   - Daftar di https://uptimerobot.com
   - Tambah monitor: HTTP Check
   - URL: `https://namaproject.up.railway.app`
   - Interval: 5 menit

---

## 12. Arsitektur di Railway

```
                    ┌─────────────────┐
                    │   Railway Edge   │
                    │   (SSL + Proxy)  │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
    ┌─────────▼──────┐ ┌────▼────┐ ┌───────▼──────┐
    │  App Service   │ │ Worker  │ │   Scheduler  │
    │  php artisan   │ │ Queue   │ │  schedule:   │
    │  serve         │ │ work    │ │  work        │
    └─────────┬──────┘ └────┬────┘ └──────────────┘
              │              │              │
              └──────────────┼──────────────┘
                             │
                    ┌────────▼────────┐
                    │   MySQL 8.0     │
                    │   (Railway)     │
                    └─────────────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
    ┌─────────▼──┐  ┌───────▼──┐  ┌───────▼──────┐
    │   Brevo    │  │  GPS API │  │  OpenRouter  │
    │  (Email)   │  │ (Fleet)  │  │  (AI Chat)   │
    └────────────┘  └──────────┘  └──────────────┘
```

### Komponen yang Harus Running

| Komponen | Service | Fungsi |
|----------|---------|--------|
| App | App Service | Melayani request HTTP |
| MySQL | Database | Menyimpan data aplikasi |
| Worker | Worker Service | Memproses job email notifikasi |
| Scheduler | Scheduler Service | Menjalankan GPS fetch setiap 30 detik |

---

## Quick Reference

### Build Command (dari `railway.json`)
```
composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan key:generate && php artisan migrate --force && php artisan storage:link
```

### Start Command (dari `railway.json`)
```
php artisan serve --host=0.0.0.0 --port=$PORT
```

### Railway CLI Commands
```bash
railway login                    # Login ke Railway
railway link                     # Link ke project
railway logs                     # Lihat log
railway run <command>            # Jalankan artisan command
railway status                   # Cek status deployment
railway variables                # Lihat environment variables
railway variables set KEY=VALUE  # Set environment variable
```

### Perintah Penting untuk Production
```bash
php artisan config:cache         # Cache config (production)
php artisan route:cache          # Cache routes (production)
php artisan view:cache           # Cache views (production)
php artisan queue:restart        # Restart queue worker setelah deploy
php artisan migrate:status       # Cek status migrasi
```

---

*Dokumen ini dibuat untuk project SILINGKAR - Sistem Informasi Layanan Lingkungan Hidup Kota Palu*
*Berbasis konfigurasi `railway.json` yang sudah ada di project*
*Terakhir diperbarui: Juli 2026*
