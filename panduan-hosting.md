# Panduan Hosting SILINGKAR (Sistem Informasi Layanan Lingkungan Hidup Kota Palu)

Panduan lengkap deployment project SILINGKAR ke berbagai platform hosting agar bisa diakses oleh masyarakat.

---

## Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Persiapan Sebelum Deploy](#2-persiapan-sebelum-deploy)
3. [Cara A: Deploy ke Railway (Recommended)](#3-cara-a-deploy-ke-railway-recommended)
4. [Cara B: Deploy ke Hostinger VPS](#4-cara-b-deploy-ke-hostinger-vps)
5. [Cara C: Deploy ke DigitalOcean](#5-cara-c-deploy-ke-digitalocean)
6. [Cara D: Deploy ke AWS](#6-cara-d-deploy-ke-aws)
7. [Cara E: Deploy ke Shared Hosting (cPanel)](#7-cara-e-deploy-ke-shared-hosting-cpanel)
8. [Cara F: Deploy dengan Docker](#8-cara-f-deploy-dengan-docker-universal)
9. [Post-Deployment Checklist](#9-post-deployment-checklist)
10. [Troubleshooting](#10-troubleshooting)
11. [Arsitektur Produksi](#11-arsitektur-produksi)

---

## 1. Persyaratan Sistem

SILINGKAR adalah aplikasi Laravel 12 yang membutuhkan:

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Node.js | 18+ (untuk build asset) |
| Composer | 2.x |
| Web Server | Nginx atau Apache (mod rewrite) |

**Ekstensi PHP yang diperlukan:**

```bash
# Cek ekstensi PHP di server
php -m
```

Pastikan ekstensi berikut aktif:
- `mbstring`
- `xml`
- `curl`
- `gd` (untuk Intervention Image)
- `pdo_mysql`
- `zip`
- `bcmath`
- `fileinfo`
- `tokenizer`

---

## 2. Persiapan Sebelum Deploy

### 2.1 Persiapan GitHub Repository

```bash
# Inisialisasi git (jika belum)
git init
git add .
git commit -m "Initial commit"

# Buat repository di GitHub, lalu push
git remote add origin https://github.com/USERNAME/dlh-palu.git
git branch -M main
git push -u origin main
```

**Pastikan file `.env.example` sudah di-commit** (sudah ada). File `.env` sendiri TIDAK boleh di-commit (sudah ada di `.gitignore`).

### 2.2 Buat Akun Layanan Eksternal

Sebelum deploy, buat akun di layanan berikut yang dibutuhkan aplikasi:

| Layanan | Fungsi | Link Daftar |
|---------|--------|-------------|
| Brevo | Email/SMTP (300 email/hari gratis) | https://app.brevo.com/account/register/ |
| OpenRouter | AI Chatbot (model gratis tersedia) | https://openrouter.ai/ |
| GPS API (opsional) | Tracking kendaraan dinas | Portal GPS masing-masing |

### 2.3 Persiapan Database

Siapkan database MySQL. Catat:
- Host
- Port (default 3306)
- Nama database
- Username
- Password

---

## 3. Cara A: Deploy ke Railway (Recommended)

Railway adalah platform PaaS (Platform as a Service) yang paling mudah untuk Laravel. Tidak perlu setup server manual.

**Biaya**: Free tier tersedia (500 jam/bulan), atau mulai $5/bulan.

### Langkah 1: Buat Akun Railway

1. Buka https://railway.app
2. Klik **Login** → pilih **Login with GitHub**
3. Izinkan akses ke GitHub

### Langkah 2: Buat Project Baru

1. Klik **New Project** → **Deploy from GitHub Repo**
2. Pilih repository `dlh-palu`
3. Railway akan mendeteksi ini sebagai project Laravel

### Langkah 3: Tambah MySQL Database

1. Di dashboard project, klik **New** → **Database** → **MySQL**
2. Railway akan membuat MySQL instance secara otomatis
3. Klik MySQL instance → tab **Variables** → salin variabel connection:
   - `MYSQL_HOST`
   - `MYSQL_PORT`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`

### Langkah 4: Konfigurasi Environment Variables

1. Klik service Laravel yang sudah di-deploy
2. Tab **Variables** → klik **Raw Editor**
3. Paste dan sesuaikan variabel berikut:

```env
# App Settings
APP_NAME="SILINGKAR DLH Kota Palu"
APP_ENV=production
APP_KEY=                          # Akan di-generate nanti
APP_DEBUG=false
APP_URL=https://namaproject.up.railway.app

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

# Database (gunakan variabel dari Railway MySQL)
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQL_DATABASE}
DB_USERNAME=${MYSQL_USER}
DB_PASSWORD=${MYSQL_PASSWORD}

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=

CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (Brevo SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-login-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-verified-email@gmail.com"
MAIL_FROM_NAME="DLH Kota Palu"

# GPS API (opsional)
GPS_LOGIN_URL=
GPS_MONITORING_URL=
GPS_USERNAME=
GPS_PASSWORD=
GPS_PALU_DEFAULT_LAT=-0.9
GPS_PALU_DEFAULT_LNG=119.87

# OpenRouter AI Chatbot
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENROUTER_MODEL=tencent/hy3:free

# Filesystem
FILESYSTEM_DISK=local
```

**Alternatif**: Klik **Variables** → **New Variable** untuk menambah satu per satu.

### Langkah 5: Tambah Build & Start Commands

1. Klik service Laravel → tab **Settings**
2. Di bagian **Build**, atur:

**Build Command:**
```bash
composer install --no-dev --optimize-autoloader &&
php artisan key:generate &&
php artisan migrate --force &&
php artisan storage:link &&
npm install &&
npm run build
```

**Start Command:**
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

> **Catatan**: Railway otomatis mendeteksi PHP dari file `composer.json`. Jika tidak terdeteksi, tambahkan file `nixpacks.toml` di root project:

```toml
[phases.setup]
nixPkgs = ["python3", "nodejs_18"]

[phases.build]
cmds = ["composer install --no-dev --optimize-autoloader", "npm install", "npm run build"]

[phases.start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
```

### Langkah 6: Tambah Queue Worker

1. Di dashboard project, klik **New** → **Empty Service**
2. Namai service ini **worker**
3. Di tab **Settings**, atur:
   - **Start Command**: `php artisan queue:work --tries=3 --timeout=60`
   - Atau gunakan **Dockerfile** custom

**Alternatif yang lebih stabil**: Buat file `Procfile` di root project:

```
web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --tries=3 --timeout=60
```

### Langkah 7: Tambah Cron Scheduler

1. Di dashboard project, klik **New** → **Empty Service**
2. Namai service ini **scheduler**
3. Di tab **Settings**, atur:
   - **Start Command**: `php artisan schedule:work`

**Alternatif**: Gabungkan dalam satu service dengan Procfile.

### Langkah 8: Generate APP_KEY

1. Buka tab **Deploy** atau **Logs** di service Laravel
2. Cari layanan Shell/Console, atau gunakan Railway CLI:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Pilih project
railway link

# Generate key
railway run php artisan key:generate

# Jalankan migration
railway run php artisan migrate --force

# Buat storage link
railway run php artisan storage:link

# Seed data (opsional)
railway run php artisan db:seed
```

### Langkah 9: Verifikasi Deployment

1. Buka URL aplikasi: `https://namaproject.up.railway.app`
2. Login admin: `admin@dlh-palu.go.id` / password yang sudah di-seed
3. Cek semua fitur:
   - Halaman publik
   - Admin panel
   - Form pengaduan
   - Upload foto
   - Email notifikasi
   - Chatbot AI

### Langkah 10: Custom Domain (Opsional)

1. Di Railway dashboard → tab **Settings** → **Networking**
2. Klik **Custom Domain**
3. Masukkan domain Anda (contoh: `dlh-palu.go.id`)
4. Railway akan memberikan DNS records yang perlu ditambahkan di registrar domain
5. Tambahkan record di panel DNS domain Anda:
   ```
   Type: CNAME
   Name: @ (atau subdomain)
   Value: namaproject.up.railway.app
   ```
6. Tunggu propagasi DNS (5 menit - 48 jam)
7. SSL/HTTPS otomatis diberikan oleh Railway

---

## 4. Cara B: Deploy ke Hostinger VPS

Hostinger menyediakan VPS dengan akses root penuh. Cocok untuk kontrol penuh atas server.

**Biaya**: Mulai ~Rp 50.000/bulan (VPS KVM 1)

### Langkah 1: Beli & Setup VPS

1. Login ke https://hpanel.hostinger.com
2. Pilih **VPS** → **Operating System**: Ubuntu 22.04 LTS
3. Catat IP Address, root password dari email
4. SSH ke VPS:

```bash
ssh root@IP_ADDRESS_ANDA
```

### Langkah 2: Update System & Install Dependencies

```bash
# Update system
apt update && apt upgrade -y

# Install PHP 8.2
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-gd php8.2-zip php8.2-bcmath \
  php8.2-tokenizer php8.2-fileinfo php8.2-dom php8.2-intl php8.2-redis

# Install Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install MySQL 8
apt install -y mysql-server
mysql_secure_installation  # Set root password & hapus test DB

# Install Nginx
apt install -y nginx

# Install Redis (opsional, untuk cache)
apt install -y redis-server

# Install Supervisor (untuk queue worker & scheduler)
apt install -y supervisor
```

### Langkah 3: Buat Database MySQL

```bash
mysql -u root -p
```

```sql
-- Ganti password dengan password kuat
CREATE DATABASE dlh_palu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dlh_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON dlh_palu.* TO 'dlh_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Langkah 4: Upload Project

**Opsi A: Git Clone (Recommended)**
```bash
cd /var/www
git clone https://github.com/USERNAME/dlh-palu.git dlh-palu
cd dlh-palu
```

**Opsi B: Upload via SCP**
```bash
# Dari komputer lokal
scp -r "D:\Backup\DLH - Palu" root@IP_ADDRESS:/var/www/dlh-palu
```

### Langkah 5: Install Dependencies & Setup

```bash
cd /var/www/dlh-palu

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies & build
npm install
npm run build

# Setup environment
cp .env.example .env

# Generate APP_KEY
php artisan key:generate
```

### Langkah 6: Konfigurasi .env

```bash
nano .env
```

Edit variabel berikut:

```env
APP_NAME="SILINGKAR DLH Kota Palu"
APP_ENV=production
APP_KEY=                    # Sudah di-generate otomatis
APP_DEBUG=false
APP_URL=https://dlh-palu.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dlh_palu
DB_USERNAME=dlh_user
DB_PASSWORD=password_kuat_anda

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-verified-email@gmail.com"
MAIL_FROM_NAME="DLH Kota Palu"

# GPS API (opsional)
GPS_LOGIN_URL=
GPS_MONITORING_URL=
GPS_USERNAME=
GPS_PASSWORD=

# OpenRouter AI Chatbot
OPENROUTER_API_KEY=your_key_here
OPENROUTER_MODEL=tencent/hy3:free
```

### Langkah 7: Jalankan Migration & Storage Link

```bash
php artisan migrate --force
php artisan db:seed         # Jika ingin import data awal
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Langkah 8: Atur Permission

```bash
# Buat user web
adduser --system --group --no-create-home www-data

# Set ownership
chown -R www-data:www-data /var/www/dlh-palu
chmod -R 755 /var/www/dlh-palu
chmod -R 775 /var/www/dlh-palu/storage
chmod -R 775 /var/www/dlh-palu/bootstrap/cache
```

### Langkah 9: Konfigurasi Nginx

```bash
nano /etc/nginx/sites-available/dlh-palu
```

Paste konfigurasi berikut:

```nginx
server {
    listen 80;
    server_name dhl-palu.go.id www.dlh-palu.go.id;
    root /var/www/dlh-palu/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Aktifkan site
ln -s /etc/nginx/sites-available/dlh-palu /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default  # Hapus default

# Test konfigurasi
nginx -t

# Restart Nginx
systemctl restart nginx
```

### Langkah 10: Setup SSL (HTTPS) dengan Certbot

```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Dapatkan sertifikat SSL
certbot --nginx -d dhl-palu.go.id -d www.dlh-palu.go.id

# Auto-renew sudah diatur otomatis, cek:
certbot renew --dry-run
```

### Langkah 11: Setup Queue Worker dengan Supervisor

```bash
nano /etc/supervisor/conf.d/dlh-palu-worker.conf
```

```ini
[program:dlh-palu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/dlh-palu/artisan queue:work --tries=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/dlh-palu/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start dlh-palu-worker:*
```

### Langkah 12: Setup Cron Scheduler

```bash
crontab -e -u www-data
```

Tambahkan baris berikut:

```cron
* * * * * cd /var/www/dlh-palu && php artisan schedule:run >> /dev/null 2>&1
```

### Langkah 13: Firewall

```bash
# Install UFW
apt install -y ufw

# Allow SSH, HTTP, HTTPS
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

---

## 5. Cara C: Deploy ke DigitalOcean

DigitalOcean menawarkan Droplet (VPS) dan App Platform (PaaS).

### Opsi 1: DigitalOcean App Platform (PaaS - Mirip Railway)

1. Buka https://cloud.digitalocean.com → **Apps** → **Create App**
2. Pilih **Source**: GitHub → pilih repository
3. Pilih branch `main`
4. Atur **Environment Variables** (sama seperti Railway di atas)
5. Tambah **Database**: **Dev Database** (MySQL) atau **Managed Database**
6. **Run Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
7. **Build Command**:
   ```bash
   composer install --no-dev --optimize-autoloader &&
   npm install &&
   npm run build
   ```
8. Deploy

### Opsi 2: DigitalOcean Droplet (VPS)

Ikuti langkah yang sama dengan **Cara B (Hostinger VPS)**. DigitalOcean punya panduan lengkap untuk Laravel:

- https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-app-with-nginx-on-ubuntu

---

## 6. Cara D: Deploy ke AWS

### Opsi 1: AWS Lightsail (Termudah di AWS)

1. Login AWS Console → **Lightsail**
2. **Create Instance** → **OS Only**: Ubuntu 22.04
3. Pilih plan ($5/bulan sudah cukup)
4. SSH ke instance, ikuti langkah seperti **Cara B (Hostinger VPS)**

### Opsi 2: AWS Elastic Beanstalk (PaaS)

1. Install EB CLI:
   ```bash
   pip install awsebcli
   eb init -p php-8.2 dlh-palu
   eb create dlh-palu-env
   ```

2. Buat file `.platform/hooks/postdeploy/01-setup.sh`:
   ```bash
   #!/bin/bash
   cd /var/app/current
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. Deploy:
   ```bash
   eb deploy
   ```

### Opsi 3: AWS EC2 (Paling Fleksibel)

Ikuti langkah seperti **Cara B (Hostinger VPS)**, tetapi di EC2 instance.

---

## 7. Cara E: Deploy ke Shared Hosting (cPanel)

Shared hosting biasanya tidak mendukung queue worker dan scheduler, sehingga beberapa fitur terbatas. **Tidak disarankan untuk produksi**.

### Langkah 1: Siapkan File untuk Upload

Di komputer lokal:

```bash
# Install dependencies & build
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Kompres seluruh folder ke .zip
# PASTIKAN hanya folder yang diperlukan:
# - app/
# - bootstrap/
# - config/
# - database/
# - lang/
# - public/
# - resources/
# - routes/
# - storage/
# - vendor/
# - .env (konfigurasi produksi)
# - artisan
# - composer.json
# - composer.lock
```

### Langkah 2: Upload via cPanel File Manager

1. Login cPanel → **File Manager**
2. Navigasi ke `/public_html/` atau subdomain
3. Upload file `.zip`
4. Extract di `/public_html/`

### Langkah 3: Setup .env

1. Edit file `.env` melalui cPanel File Manager
2. Set variabel sesuai panduan

### Langkah 4: Setup Database via cPanel

1. cPanel → **MySQL Databases**
2. Buat database: `dlh_palu`
3. Buat user database: `dlh_user` dengan password
4. Tambahkan user ke database dengan **All Privileges**
5. Catat: host, database name, username, password

### Langkah 5: Import Database

1. cPanel → **phpMyAdmin**
2. Pilih database `dlh_palu`
3. Klik **Import** → pilih file `.sql` dari migration

**Alternatif**: Jalankan migration via SSH (jika tersedia):
```bash
php artisan migrate --force
php artisan db:seed
```

### Langkah 6: Setup Storage Link

Jika shared hosting tidak mendukung symlink:
- Buka file `config/filesystems.php`
- Pastikan `public` disk sudah benar
- Copy isi `storage/app/public/` secara manual ke `public/storage/`

### Keterbatasan Shared Hosting

| Fitur | Status | Solusi |
|-------|--------|--------|
| Queue Worker | Tidak jalan | Gunakan scheduler manual atau third-party service |
| Cron Scheduler | Terbatas (1 menit) | Set via cPanel Cron Jobs: `* * * * * cd /path/to/project && php artisan schedule:run` |
| Supervisor | Tidak tersedia | Gunakan cron untuk queue: `* * * * * cd /path && php artisan queue:work --stop-when-empty` |
| SSH Access | Terbatas | Gunakan cPanel Terminal atau pelanggan SSH |

---

## 9. Post-Deployment Checklist

Setelah deploy, verifikasi hal berikut:

### 9.1 Checklist Wajib

- [ ] Akses halaman publik → tampil dengan benar
- [ ] Login admin berhasil
- [ ] Form pengaduan masyarakat bisa diisi dan dikirim
- [ ] Upload foto laporan berhasil
- [ ] Email notifikasi terkirim (cek Brevo dashboard)
- [ ] Chatbot AI merespons
- [ ] GPS tracking berfungsi (jika dikonfigurasi)
- [ ] Admin panel bisa CRUD semua data
- [ ] Export CSV berfungsi
- [ ] PDF laporan bisa di-download
- [ ] Statistik dashboard tampil dengan benar

### 9.2 Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` sudah di-generate
- [ ] `.env` tidak bisa diakses dari browser
- [ ] HTTPS aktif
- [ ] Rate limiting aktif (sudah built-in Laravel)
- [ ] Database user punya permission minimum yang diperlukan
- [ ] Folder `storage` dan `bootstrap/cache` punya permission 775

### 9.3 Performance Checklist

- [ ] `php artisan config:cache` sudah dijalankan
- [ ] `php artisan route:cache` sudah dijalankan
- [ ] `php artisan view:cache` sudah dijalankan
- [ ] `composer install --no-dev --optimize-autoloader` sudah dijalankan
- [ ] Asset sudah di-build (`npm run build`)
- [ ] GZIP compression aktif di Nginx/Apache

### 9.4 Monitoring

- [ ] Log error bisa diakses: `storage/logs/laravel.log`
- [ ] Monitoring uptime (gunakan UptimeRobot.com - gratis)
- [ ] Backup database terjadwal

---

## 10. Troubleshooting

### Error 500 Internal Server Solution

```bash
# Cek log error
tail -f storage/logs/laravel.log

# Pastikan permission benar
chmod -R 775 storage bootstrap/cache

# Pastikan .env sudah dikonfigurasi
cat .env

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Error "No Application Encryption Key Has Been Specified"

```bash
php artisan key:generate
```

### Error Database Connection

```bash
# Pastikan MySQL running
mysql -u dlh_user -p

# Cek .env
grep DB_ .env

# Test koneksi
php artisan migrate:status
```

### Storage/Symlink Issues

```bash
# Buat ulang symlink
rm public/storage
php artisan storage:link

# Atau manual (jika symlink tidak didukung)
cp -r storage/app/public/* public/storage/
```

### Queue Tidak Jalan

```bash
# Cek status queue
php artisan queue:restart

# Jalankan queue worker
php artisan queue:work --tries=3 --timeout=60

# Cek job di database
php artisan tinker
>>> App\Models\EmailNotificationLog::count()
```

### Build Asset Gagal

```bash
# Clear node_modules
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 11. Arsitektur Produksi

Berikut diagram arsitektur ideal untuk SILINGKAR di produksi:

```
                    ┌─────────────────┐
                    │   CDN / Cloudflare │
                    │   (SSL + Cache)   │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │   Nginx / Apache │
                    │   (Reverse Proxy)│
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
    ┌─────────▼──────┐ ┌────▼────┐ ┌───────▼──────┐
    │  PHP-FPM (Web) │ │ Worker  │ │   Scheduler  │
    │  Laravel App   │ │ Queue   │ │  Cron/Artisan│
    └─────────┬──────┘ └────┬────┘ └──────────────┘
              │              │              │
              └──────────────┼──────────────┘
                             │
                    ┌────────▼────────┐
                    │   MySQL 8.0     │
                    │   Database      │
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

| Komponen | Fungsi | Perintah |
|----------|--------|----------|
| Web Server | Melayani request HTTP | `nginx` / `php-fpm` |
| PHP-FPM | Menjalankan aplikasi Laravel | `systemctl start php8.2-fpm` |
| Queue Worker | Memproses job email notifikasi | `php artisan queue:work` |
| Scheduler | Menjalankan GPS fetch setiap 30 detik | `php artisan schedule:work` |
| MySQL | Database | `systemctl start mysql` |
| Supervisor | Monitor queue worker | `systemctl start supervisor` |

---

## Ringkasan: Platform Mana yang Dipilih?

| Platform | Biaya | Kemudahan | Kontrol | Cocok Untuk |
|----------|-------|-----------|---------|-------------|
| **Railway** | Free tier / $5/bln | ⭐⭐⭐⭐⭐ | Terbatas | Quick start, MVP |
| **Hostinger VPS** | ~Rp 50.000/bln | ⭐⭐⭐ | Penuh | Produksi, budget rendah |
| **DigitalOcean** | $5/bln | ⭐⭐⭐⭐ | Penuh | Fleksibel, skalabel |
| **AWS Lightsail** | $5/bln | ⭐⭐⭐ | Penuh | Enterprise, AWS ecosystem |
| **Shared Hosting** | ~Rp 20.000/bln | ⭐⭐⭐⭐ | Sangat terbatas | Demo, testing |

**Rekomendasi untuk SILINGKAR**:
- **Demo/Testing**: Railway (gratis, cepat setup)
- **Produksi Pemerintah**: Hostinger VPS atau DigitalOcean (kontrol penuh, biaya rendah)
- **Skala Besar**: AWS atau Google Cloud (enterprise features)

---

## 8. Cara F: Deploy dengan Docker (Universal)

Project sudah dilengkapi Dockerfile dan docker-compose.yml. Metode ini bisa digunakan di **semua platform** yang mendukung Docker.

### Build & Run di Lokal

```bash
# Build image
docker-compose build

# Jalankan semua service
docker-compose up -d

# Jalankan migration
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed

# Buat storage link
docker-compose exec app php artisan storage:link

# Lihat log
docker-compose logs -f
```

### Deploy Docker Image ke Cloud

```bash
# Build image
docker build -t dlh-palu:latest .

# Push ke Docker Hub
docker tag dlh-palu:latest username/dlh-palu:latest
docker push username/dlh-palu:latest

# Deploy ke Railway (gunakan Dockerfile)
# Railway akan mendeteksi Dockerfile otomatis

# Deploy ke DigitalOcean App Platform
# Upload Dockerfile ke repository, Railway akan build otomatis
```

### File yang Disediakan

| File | Fungsi |
|------|--------|
| `Dockerfile` | Build image PHP-FPM + Nginx + Node.js |
| `docker-compose.yml` | Orchestration: App + Nginx + MySQL + Queue + Scheduler |
| `nginx.conf` | Konfigurasi Nginx reverse proxy ke PHP-FPM |
| `.dockerignore` | File yang dikecualikan dari Docker build |

---

## Catatan Tambahan

### Backup Database

```bash
# Backup
mysqldump -u dlh_user -p dlh_palu > backup_$(date +%Y%m%d).sql

# Restore
mysql -u dlh_user -p dlh_palu < backup_20240101.sql
```

### Update Aplikasi di Produksi

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migration (jika ada perubahan database)
php artisan migrate --force

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
php artisan queue:restart
```

### Monitoring Log

```bash
# Tail log error
tail -f storage/logs/laravel.log

# Cek queue status
php artisan queue:work --once  # Proses 1 job saja
```

---

*Dokumen ini dibuat untuk project SILINGKAR - Sistem Informasi Layanan Lingkungan Hidup Kota Palu*
*Terakhir diperbarui: Juli 2026*
