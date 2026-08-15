<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyewaan taman kini mendukung 5 taman resmi (dropdown terbatas) maupun
     * taman lain secara manual ("Lainnya"). Kolom nama_taman_manual menampung
     * nama taman manual, dan taman_kota_id dibuat nullable agar boleh kosong
     * saat pemohon memilih opsi "Lainnya".
     */
    public function up(): void
    {
        Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
            $table->string('nama_taman_manual')->nullable();
            $table->foreignId('taman_kota_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_pinjam_tamans', function (Blueprint $table) {
            $table->dropColumn('nama_taman_manual');
            $table->foreignId('taman_kota_id')->nullable(false)->change();
        });
    }
};
