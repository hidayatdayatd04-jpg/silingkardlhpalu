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
        // Laporan
        Schema::table('laporans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nomor_hp');
        });

        // PengaduanTataPenataan
        Schema::table('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('no_hp');
        });

        // PerizinanTebangPohon - akan ditambahkan di migration khusus
        // Schema::table('perizinan_tebang_pohons', function (Blueprint $table) {
        //     $table->string('email')->nullable()->after('nomor_hp');
        // });

        // PermohonanPinjamTaman - akan ditambahkan di migration khusus
        // Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
        //     $table->string('email')->nullable()->after('nomor_hp');
        // });

        // RegistrasiUsahaLb3 - akan ditambahkan di migration khusus
        // Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
        //     $table->string('email')->nullable()->after('nomor_telepon');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        // Schema::table('perizinan_tebang_pohons', function (Blueprint $table) {
        //     $table->dropColumn('email');
        // });

        // Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
        //     $table->dropColumn('email');
        // });

        // Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
        //     $table->dropColumn('email');
        // });
    }
};
