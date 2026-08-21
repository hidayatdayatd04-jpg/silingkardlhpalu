<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('gps:fetch')->everyThirtySeconds();

// Safety net backup/restore: proses queue database tiap menit tanpa perlu worker daemon
// (shared hosting). --stop-when-empty agar tidak block, --timeout=1900 sesuai RunBackupJob.
Schedule::command('queue:work --stop-when-empty --timeout=1900 --sleep=2 --tries=1 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Bersihkan file yatim di B2 (objek tanpa referensi di database) tiap Minggu 03:00.
Schedule::command('dlh:cleanup-orphan-files --delete')->weeklyOn(0, '03:00')->onOneServer();
