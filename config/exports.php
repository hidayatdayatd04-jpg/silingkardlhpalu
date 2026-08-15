<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ekspor Antrean (queued export) — Task 12
    |--------------------------------------------------------------------------
    |
    | Saat true, export "filter" / "Semua Data" / "Terpilih" TIDAK lagi
    | diproses sinkron di dalam request (yang bisa memblokir respons saat
    | baris berjumlah ribuan). Sebagai gantinya:
    |   1. Job `GenerateExportJob` di-queue (butuh QUEUE_CONNECTION=redis
    |      atau database + queue worker berjalan).
    |   2. File dibuat di storage/app/private/exports/....
    |   3. User mendapat notifikasi berisi tautan unduh.
    |
    | Saat FALSE (default), perilaku tetap seperti sekarang: unduh langsung
    | (sinkron) tanpa mengubah UX.
    */

    'queue' => env('QUEUE_EXPORTS', false),

    /*
    | Direktori relatif (di bawah storage/app/private/) tempat file ekspor
    | berantri disimpan sebelum diunduh.
    */
    'storage_dir' => 'exports',

];