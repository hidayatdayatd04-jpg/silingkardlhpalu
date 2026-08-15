<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_data_layers', function (Blueprint $table) {
            $table->id();
            $table->string('bidang'); // pengendalian, sampah-lb3, rth, tata-penataan
            $table->string('nama_layer');
            $table->string('deskripsi')->nullable();
            $table->string('jenis_geometri')->default('point');
            $table->json('geojson_features'); // Array of GeoJSON features
            $table->json('metadata')->nullable(); // warna, ukuran marker, opacity, dll
            $table->boolean('is_visible')->default(true);
            $table->integer('z_index')->default(0);
            $table->timestamps();

            $table->index('bidang');
            $table->index(['bidang', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_data_layers');
    }
};
