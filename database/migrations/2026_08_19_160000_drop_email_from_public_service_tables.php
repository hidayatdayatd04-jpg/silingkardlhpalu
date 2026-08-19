<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. permohonan_rekomendasis / permohonan_rekomendasi
        $tablePermohonan = Schema::hasTable('permohonan_rekomendasis') ? 'permohonan_rekomendasis' : (Schema::hasTable('permohonan_rekomendasi') ? 'permohonan_rekomendasi' : null);
        if ($tablePermohonan && Schema::hasColumn($tablePermohonan, 'email')) {
            Schema::table($tablePermohonan, function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 2. registrasi_usaha_lb3
        $tableLb3 = Schema::hasTable('registrasi_usaha_lb3') ? 'registrasi_usaha_lb3' : (Schema::hasTable('registrasi_usaha_lb3s') ? 'registrasi_usaha_lb3s' : null);
        if ($tableLb3 && Schema::hasColumn($tableLb3, 'email')) {
            Schema::table($tableLb3, function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 3. pengajuan_rintek_pertek
        $tableRintek = Schema::hasTable('pengajuan_rintek_pertek') ? 'pengajuan_rintek_pertek' : (Schema::hasTable('pengajuan_rintek_perteks') ? 'pengajuan_rintek_perteks' : null);
        if ($tableRintek && Schema::hasColumn($tableRintek, 'email')) {
            Schema::table($tableRintek, function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 4. permohonan_pinjam_taman
        $tableTaman = Schema::hasTable('permohonan_pinjam_taman') ? 'permohonan_pinjam_taman' : (Schema::hasTable('permohonan_pinjam_tamans') ? 'permohonan_pinjam_tamans' : null);
        if ($tableTaman && Schema::hasColumn($tableTaman, 'email')) {
            Schema::table($tableTaman, function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablePermohonan = Schema::hasTable('permohonan_rekomendasis') ? 'permohonan_rekomendasis' : (Schema::hasTable('permohonan_rekomendasi') ? 'permohonan_rekomendasi' : null);
        if ($tablePermohonan && ! Schema::hasColumn($tablePermohonan, 'email')) {
            Schema::table($tablePermohonan, function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }

        $tableLb3 = Schema::hasTable('registrasi_usaha_lb3') ? 'registrasi_usaha_lb3' : (Schema::hasTable('registrasi_usaha_lb3s') ? 'registrasi_usaha_lb3s' : null);
        if ($tableLb3 && ! Schema::hasColumn($tableLb3, 'email')) {
            Schema::table($tableLb3, function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }

        $tableRintek = Schema::hasTable('pengajuan_rintek_pertek') ? 'pengajuan_rintek_pertek' : (Schema::hasTable('pengajuan_rintek_perteks') ? 'pengajuan_rintek_perteks' : null);
        if ($tableRintek && ! Schema::hasColumn($tableRintek, 'email')) {
            Schema::table($tableRintek, function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }

        $tableTaman = Schema::hasTable('permohonan_pinjam_taman') ? 'permohonan_pinjam_taman' : (Schema::hasTable('permohonan_pinjam_tamans') ? 'permohonan_pinjam_tamans' : null);
        if ($tableTaman && ! Schema::hasColumn($tableTaman, 'email')) {
            Schema::table($tableTaman, function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }
    }
};
