<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objek_pengawasan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('nama_penanggung_jawab');
            $table->string('jenis_usaha')->nullable();
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('objek_pengawasans_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained('objek_pengawasan')->cascadeOnDelete();
            $table->string('jenis_dokumen');
            $table->string('status_dokumen')->default('tidak_ada');
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->unique(['objek_pengawasan_id', 'jenis_dokumen'], 'objek_dokumen_unique');
        });

        // Tabel pengaduan_tata_penataan & pengaduan_tata_penataan_foto dibuat
        // di migration 2026_08_17_100000_restructure_pengaduan_tables.php.

        Schema::create('sidak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained('objek_pengawasan')->cascadeOnDelete();
            // FK ke pengaduan_tata_penataan ditambahkan di migration
            // 2026_08_17_100000_restructure_pengaduan_tables.php.
            $table->unsignedBigInteger('pengaduan_tata_penataan_id')->nullable();
            $table->date('tanggal_sidak');
            $table->string('nama_petugas');
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->string('hasil')->nullable();
            $table->text('temuan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->string('status_tindak_lanjut')->default('Belum Ditindaklanjuti');
            $table->boolean('is_jadwal')->default(false);
            $table->text('catatan_jadwal')->nullable();
            $table->timestamps();
        });

        Schema::create('sidak_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidak_id')->constrained('sidak')->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('foto');
            $table->timestamps();
        });

        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_pengawasan_id')->constrained('objek_pengawasan')->cascadeOnDelete();
            $table->foreignId('sidak_id')->nullable()->constrained('sidak')->nullOnDelete();
            $table->string('jenis_pelanggaran');
            $table->string('pasal_dilanggar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pelanggaran_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggaran_id')->constrained('pelanggarans')->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('foto');
            $table->timestamps();
        });

        Schema::create('sanksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggaran_id')->constrained('pelanggarans')->cascadeOnDelete();
            $table->string('jenis_sanksi');
            $table->date('batas_waktu_perbaikan')->nullable();
            $table->string('status_sanksi')->default('Belum Ditindaklanjuti');
            $table->string('surat_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('sosialisasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('tanggal');
            $table->text('materi');
            $table->text('hasil_evaluasi')->nullable();
            $table->timestamps();
        });

        Schema::create('sosialisasi_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sosialisasi_id')->constrained('sosialisasi')->cascadeOnDelete();
            $table->foreignId('objek_pengawasan_id')->constrained('objek_pengawasan')->cascadeOnDelete();
            $table->string('sertifikat_path')->nullable();
            $table->timestamps();

            $table->unique(['sosialisasi_id', 'objek_pengawasan_id'], 'sosialisasi_peserta_unique');
        });

        Schema::create('sosialisasi_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sosialisasi_id')->constrained('sosialisasi')->cascadeOnDelete();
            $table->string('path');
            $table->string('tipe')->default('materi');
            $table->string('nama')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosialisasi_file');
        Schema::dropIfExists('sosialisasi_peserta');
        Schema::dropIfExists('sosialisasi');
        Schema::dropIfExists('sanksi');
        Schema::dropIfExists('pelanggaran_media');
        Schema::dropIfExists('pelanggarans');
        Schema::dropIfExists('sidak_media');
        Schema::dropIfExists('sidak');
        Schema::dropIfExists('objek_pengawasans_dokumen');
        Schema::dropIfExists('objek_pengawasan');
    }
};
