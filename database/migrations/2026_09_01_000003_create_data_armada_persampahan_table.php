<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_armada_persampahan', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->index();
            $table->string('jenis_kendaraan');
            $table->string('merk_type');
            $table->string('tahun_perolehan', 20);
            $table->string('nomor_polisi', 50)->nullable();
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('kondisi', 50)->nullable()->default('Baik');
            $table->text('keterangan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_armada_persampahan');
    }
};
