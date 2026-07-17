<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Jenis Usaha harus dibuat sebelum objek_pengawasans
        Schema::create('jenis_usahas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        // Tabel Jenis LB3 harus dibuat sebelum registrasi_usaha_lb3s
        Schema::create('jenis_lb3s', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_lb3s');
        Schema::dropIfExists('jenis_usahas');
    }
};
