<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas kuota untuk progress bar monitoring infrastruktur.
    | Nilai default mengikuti free-tier masing-masing layanan dan dapat
    | disesuaikan lewat file .env (B2_STORAGE_LIMIT_GB / NEON_STORAGE_LIMIT_GB).
    |--------------------------------------------------------------------------
    */

    'b2_limit_bytes' => (float) env('B2_STORAGE_LIMIT_GB', 10) * 1024 * 1024 * 1024,

    'neon_limit_bytes' => (float) env('NEON_STORAGE_LIMIT_GB', 0.5) * 1024 * 1024 * 1024,

    /*
    |--------------------------------------------------------------------------
    | Status paket/langganan untuk ditampilkan pada card monitoring.
    | B2 & Neon tidak mengekspos plan lewat kredensial storage/DB biasa,
    | sehingga status diisi manual lewat .env (default: Free Tier).
    | Contoh: "Free Tier", "Backblaze B2 Cloud Storage", "Neon Scale", dll.
    |--------------------------------------------------------------------------
    */

    'b2_plan' => env('B2_PLAN', 'Free Tier'),

    'neon_plan' => env('NEON_PLAN', 'Free Tier'),

];
