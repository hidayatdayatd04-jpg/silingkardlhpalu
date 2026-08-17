<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $excludedLayers = ['Taman Kota', 'Hutan Kota', 'Jalur Hijau', 'Pohon Pelindung', 'Aset RTH'];

        DB::table('gis_data_layer')
            ->whereIn('nama_layer', $excludedLayers)
            ->where('bidang', 'rth')
            ->delete();
    }

    public function down(): void
    {
        // These layers are removed permanently; no rollback needed.
    }
};
