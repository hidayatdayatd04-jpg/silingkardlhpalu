<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objek_pengawasans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('nama_penanggung_jawab');
            $table->foreignId('jenis_usaha_id')->nullable()->constrained('jenis_usahas')->nullOnDelete();
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('objek_pengawasan_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_dokumen');
            $table->string('status_dokumen')->default('tidak_ada');
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->unique(['objek_pengawasan_id', 'jenis_dokumen'], 'objek_dokumen_unique');
        });

        Schema::create('pengaduan_tata_penataans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket')->unique();
            $table->string('nama_pelapor');
            $table->string('no_hp');
            $table->string('jenis_pengaduan');
            $table->string('nama_terlapor')->nullable();
            $table->string('nama_perusahaan_terlapor')->nullable();
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('deskripsi');
            $table->string('status')->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pengaduan_tata_penataan_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_tata_penataan_id')->constrained()->cascadeOnDelete();
            $table->string('path_foto');
            $table->timestamps();
        });

        Schema::create('sidaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pengaduan_tata_penataan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tanggal_sidak');
            $table->string('nama_petugas');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hasil')->nullable();
            $table->text('temuan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->string('status_tindak_lanjut')->default('belum');
            $table->boolean('is_jadwal')->default(false);
            $table->text('catatan_jadwal')->nullable();
            $table->timestamps();
        });

        Schema::create('sidak_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidak_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('foto');
            $table->timestamps();
        });

        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sidak_id')->nullable()->constrained()->nullOnDelete();
            $table->string('jenis_pelanggaran');
            $table->string('pasal_dilanggar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pelanggaran_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggaran_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('foto');
            $table->timestamps();
        });

        Schema::create('sanksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggaran_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_sanksi');
            $table->date('batas_waktu_perbaikan')->nullable();
            $table->string('status_sanksi')->default('diberikan');
            $table->string('surat_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('sosialisasis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('tanggal');
            $table->text('materi');
            $table->text('hasil_evaluasi')->nullable();
            $table->timestamps();
        });

        Schema::create('sosialisasi_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sosialisasi_id')->constrained()->cascadeOnDelete();
            $table->foreignId('objek_pengawasan_id')->constrained()->cascadeOnDelete();
            $table->string('sertifikat_path')->nullable();
            $table->timestamps();

            $table->unique(['sosialisasi_id', 'objek_pengawasan_id'], 'sosialisasi_peserta_unique');
        });

        Schema::create('sosialisasi_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sosialisasi_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('materi');
            $table->string('nama')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosialisasi_files');
        Schema::dropIfExists('sosialisasi_pesertas');
        Schema::dropIfExists('sosialisasis');
        Schema::dropIfExists('sanksis');
        Schema::dropIfExists('pelanggaran_media');
        Schema::dropIfExists('pelanggarans');
        Schema::dropIfExists('pengaduan_tata_penataan_fotos');
        Schema::dropIfExists('pengaduan_tata_penataans');
        Schema::dropIfExists('sidak_media');
        Schema::dropIfExists('sidaks');
        Schema::dropIfExists('objek_pengawasan_dokumens');
        Schema::dropIfExists('objek_pengawasans');
    }
};
