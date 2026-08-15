<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_fotos', function (Blueprint $table) {
            $table->string('path_foto')->nullable()->change();
        });

        Schema::table('pengaduan_tata_penataan_fotos', function (Blueprint $table) {
            $table->string('path_foto')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_fotos', function (Blueprint $table) {
            $table->string('path_foto')->nullable(false)->change();
        });

        Schema::table('pengaduan_tata_penataan_fotos', function (Blueprint $table) {
            $table->string('path_foto')->nullable(false)->change();
        });
    }
};
