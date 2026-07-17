<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'laporans',
        'pengaduan_tata_penataans',
        'permohonan_rekomendasis',
        'pengajuan_rintek_perteks',
        'perizinan_tebang_pohons',
        'permohonan_pinjam_tamans',
        'registrasi_usaha_lb3s',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'sla_deadline')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dateTime('sla_deadline')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sla_deadline')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('sla_deadline');
                });
            }
        }
    }
};
