<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permohonan_pinjam_taman')) {
            Schema::create('permohonan_pinjam_taman', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_tiket', 20)->unique();
                $table->string('nama_pemohon');
                $table->string('nama_kegiatan');
                $table->string('nama_taman')->nullable();
                $table->dateTime('tanggal_kegiatan');
                $table->dateTime('tanggal_selesai')->nullable();
                $table->string('surat_permohonan');
                $table->boolean('jaminan_kebersihan')->default(false);
                $table->string('status')->default('Belum Ditindaklanjuti');
                $table->text('catatan_admin')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('data_tanam_pohon')) {
            Schema::create('data_tanam_pohon', function (Blueprint $table) {
                $table->id();
                $table->string('nama_penanggung_jawab');
                $table->unsignedInteger('jumlah_pohon');
                $table->string('jenis_pohon');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->json('foto_dokumentasi')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_tanam_pohon');
        Schema::dropIfExists('permohonan_pinjam_taman');
    }
};
