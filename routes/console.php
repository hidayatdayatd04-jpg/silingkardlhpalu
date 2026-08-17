<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('gps:fetch')->everyThirtySeconds();

// Bersihkan file yatim di B2 (objek tanpa referensi di database) tiap Minggu 03:00.
Schedule::command('dlh:cleanup-orphan-files --delete')->weeklyOn(0, '03:00')->onOneServer();
