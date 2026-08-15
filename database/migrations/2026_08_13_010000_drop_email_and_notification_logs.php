<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom email dari resource pengaduan (fitur notifikasi email/WhatsApp
     * sudah dihapus atas instruksi atasan). Resource lain tetap mempertahankan email.
     */
    public function up(): void
    {
        // Laporan dipakai bersama pengaduan-pengendalian, pengaduan-sampah, pengaduan-rth.
        Schema::table('laporans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        // Tabel log pengiriman notifikasi WA/email sudah tidak terpakai.
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('email_notification_logs');
        Schema::dropIfExists('whatsapp_notification_logs');
    }

    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        Schema::table('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        // Tabel log tidak dibuat ulang di rollback (data historis WA/email sudah dihapus).
    }
};
