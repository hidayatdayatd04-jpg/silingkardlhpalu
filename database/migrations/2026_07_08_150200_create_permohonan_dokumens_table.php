<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_rekomendasi_id')->constrained('permohonan_rekomendasis')->cascadeOnDelete();
            $table->string('path_dokumen');
            $table->string('nama_dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_dokumens');
    }
};
