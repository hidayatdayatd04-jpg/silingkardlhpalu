<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Map old PengaduanStatus values to new ones for Pengendalian & Sampah records
        $oldToNew = [
            'Belum Ditinjau' => 'Belum Ditindaklanjuti',
            'Ditinjau' => 'Ditindaklanjuti',
            'Selesai' => 'Ditindaklanjuti',
            'Ditolak' => 'Ditindaklanjuti',
        ];

        foreach ($oldToNew as $old => $new) {
            DB::table('laporans')
                ->where('bidang', '!=', 'rth')
                ->where('status', $old)
                ->update(['status' => $new]);
        }
    }

    public function down(): void
    {
        // Reverse mapping — best effort, may lose info for Selesai/Ditolak
        $newToOld = [
            'Belum Ditindaklanjuti' => 'Belum Ditinjau',
            'Ditindaklanjuti' => 'Ditinjau',
        ];

        // Only reverse Belum Ditindaklanjuti back (safe)
        DB::table('laporans')
            ->where('bidang', '!=', 'rth')
            ->where('status', 'Belum Ditindaklanjuti')
            ->update(['status' => 'Belum Ditinjau']);
    }
};
