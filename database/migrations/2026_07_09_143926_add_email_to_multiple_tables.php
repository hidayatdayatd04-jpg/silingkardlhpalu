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
            $table->string('email')->nullable();
        });

        // PengaduanTataPenataan
        Schema::table('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        // PermohonanPinjamTaman - akan ditambahkan di migration khusus
        // Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
        //     $table->string('email')->nullable();
        // });

        // RegistrasiUsahaLb3 - akan ditambahkan di migration khusus
        // Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
        //     $table->string('email')->nullable();
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

        // Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
        //     $table->dropColumn('email');
        // });

        // Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
        //     $table->dropColumn('email');
        // });
    }
};
