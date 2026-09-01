<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_tpu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tpu');
            $table->string('luas_area_makam', 100);
            $table->json('vegetasi')->nullable();
            $table->json('kapasitas_blok')->nullable();
            $table->string('foto_dokumentasi_1')->nullable();
            $table->string('foto_dokumentasi_2')->nullable();
            $table->string('foto_dokumentasi_3')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_tpu');
    }
};
