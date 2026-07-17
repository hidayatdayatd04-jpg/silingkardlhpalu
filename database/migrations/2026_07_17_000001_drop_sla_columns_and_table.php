<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'laporans',
            'pengaduan_tata_penataans',
            'permohonan_rekomendasis',
            'pengajuan_rintek_perteks',
            'perizinan_tebang_pohons',
            'permohonan_pinjam_tamans',
            'registrasi_usaha_lb3s',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'sla_deadline')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('sla_deadline');
                });
            }
        }

        if (Schema::hasTable('sla_settings')) {
            Schema::dropIfExists('sla_settings');
        }
    }

    public function down(): void
    {
        // Not reversible — old SLA data is gone.
    }
};
