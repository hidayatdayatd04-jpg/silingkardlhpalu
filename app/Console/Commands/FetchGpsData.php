<?php

namespace App\Console\Commands;

use App\Services\GpsService;
use Illuminate\Console\Command;

class FetchGpsData extends Command
{
    protected $signature = 'gps:fetch';

    protected $description = 'Fetch and cache GPS vehicle location tracking details from GPS.id API';

    public function handle(GpsService $gpsService)
    {
        $this->info('Starting GPS cache sync...');

        $success = $gpsService->fetchAndCache();

        if ($success) {
            $this->info('GPS cache sync completed successfully.');

            return 0;
        } else {
            $this->error('GPS cache sync failed.');

            return 1;
        }
    }
}
