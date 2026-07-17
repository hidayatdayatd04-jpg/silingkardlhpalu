<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_rekomendasis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket')->unique();
            $table->string('nama_perusahaan');
            $table->string('nama_pemilik');
            $table->string('npwp', 20);
            $table->string('jenis_usaha');
            $table->foreignId('jenis_usaha_id')->nullable()->constrained('jenis_usahas')->nullOnDelete();
            $table->text('alamat_lengkap');
            $table->string('nomor_telepon', 20);
            $table->string('email');
            $table->string('jenis_pengajuan');
            $table->string('surat_permohonan');
            $table->string('status')->default('Belum Ditindaklanjuti');
            $table->text('catatan_verifikasi')->nullable();
            $table->boolean('dokumen_lengkap_terverifikasi')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_rekomendasis');
    }
};
