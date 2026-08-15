<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Koordinat pengaduan bersifat opsional di form admin (dan sebagian
     * pengaduan tidak memiliki titik lokasi). Kolom dibuat nullable agar
     * konsisten dengan aturan validasi 'nullable' dan tidak memicu error
     * NOT NULL saat admin menyimpan tanpa mengisi koordinat.
     */
    public function up(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable(false)->change();
            $table->decimal('longitude', 11, 8)->nullable(false)->change();
        });
    }
};
