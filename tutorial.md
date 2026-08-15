1. Install Prerequisites
- PHP 8.2+ (termasuk extensions: openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath, fileinfo, curl, zip)
- Composer (https://getcomposer.org)
- Node.js 18+ & npm
- MySQL / MariaDB
2. Copy Project ke XAMPP
Copy folder "DLH - Project" ke C:\xampp\htdocs\
Rename jadi "DLH - Palu"
3. Setup Environment
cd "C:\xampp\htdocs\DLH - Palu"
copy .env.example .env
Edit file .env, isi database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dlh_palu
DB_USERNAME=root
DB_PASSWORD=
4. Install Dependencies
composer install
npm install
5. Generate App Key & Setup Database
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan db:seed --class=ProfilDinasSeeder (jika seed satu file aja)
6. Build Frontend & Run
npm run build
php artisan serve
Buka browser: http://localhost:8000
---
Yang TIDAK di-backup (dipulihkan otomatis):
- node_modules → npm install
- vendor → composer install
- .git → git init (jika perlu)
- .env → buat baru dari .env.example
Backup ini sudah siap dipindahkan ke laptop baru!

noted : APP_URL=http://localhost:8000