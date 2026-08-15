#!/usr/bin/env bash
#
# Task 7 — Optimasi bootstrap cache Laravel untuk production.
#
# Jalankan di server SETELAH `composer install --no-dev` dan SEBELUM server
# menerima traffic baru (atau saat deploy/restart PHP-FPM).
#
# Catatan:
#  - `route:cache` hanya bisa jalan jika routes/web.php tidak memakai closure.
#    Repo ini masih memakai closure di beberapa route publik, jadi perintah
#    tersebut dijalankan secara opsional (gagal => fallback route:clear).
#    Sebelum mengaktifkan route:cache penuh, konversikan semua route ke
#    Controller::class reference dulu.
#  - Jika OPcache aktif dengan validate_timestamps=0, panggil opcache_reset()
#    agar cache bytecode tidak menyimpan versi lama.
#
set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> config:cache"
php artisan config:cache

echo "==> event:cache"
php artisan event:cache

echo "==> view:cache"
php artisan view:cache

echo "==> route:cache (opsional)"
if php artisan route:cache; then
    echo "    route:cache OK"
else
    echo "    <> route:cache GAGAL (kemungkinan ada closure di routes/web.php)."
    php artisan route:clear
fi

# OPcache (Task 13): pastikan aktif di production.
#  - cek manual : php -i | grep opcache.enable
#  - contoh konfigurasi ada di scripts/opcache.ini.example
if php -r 'echo ini_get("opcache.enable") ? "1" : "0";' | grep -q 1; then
    echo "    opcache.enable = 1"
    echo "==> opcache_reset()"
    php -r 'if (function_exists("opcache_reset")) opcache_reset();'
else
    echo "    !!! opcache.enable = 0 (matikan) — aktifkan via php.ini, lihat scripts/opcache.ini.example"
fi

echo "==> Selesai. Isi bootstrap/cache:"
ls -1 bootstrap/cache/ | grep -E '^(config|events|routes|views)' || true