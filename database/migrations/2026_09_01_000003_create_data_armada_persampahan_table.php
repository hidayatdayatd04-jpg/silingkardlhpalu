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
            $table->string('merk_type');
            $table->string('tahun_perolehan', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_armada_persampahan');
    }
};
