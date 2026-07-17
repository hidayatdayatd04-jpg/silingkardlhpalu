<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('perizinan_tebang_pohons')) {
            Schema::create('perizinan_tebang_pohons', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_tiket', 20)->unique();
                $table->string('nama_pemohon');
                $table->string('surat_permohonan');
                $table->string('ktp_nib');
                $table->text('alasan_penebangan');
                $table->string('foto_pohon');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->text('rencana_ganti_tanam');
                $table->string('status')->default('Belum Ditinjau');
                $table->text('catatan_survei')->nullable();
                $table->string('keputusan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permohonan_pinjam_tamans')) {
            Schema::create('permohonan_pinjam_tamans', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_tiket', 20)->unique();
                $table->string('nama_pemohon');
                $table->string('nama_kegiatan');
                $table->foreignId('taman_kota_id')->constrained('taman_kotas')->cascadeOnDelete();
                $table->dateTime('tanggal_kegiatan');
                $table->dateTime('tanggal_selesai')->nullable();
                $table->string('surat_permohonan');
                $table->boolean('jaminan_kebersihan')->default(false);
                $table->string('surat_jaminan')->nullable();
                $table->string('status')->default('Belum Ditinjau');
                $table->text('catatan_admin')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('data_tanam_pohons')) {
            Schema::create('data_tanam_pohons', function (Blueprint $table) {
                $table->id();
                $table->string('nama_penanggung_jawab');
                $table->unsignedInteger('jumlah_pohon');
                $table->string('jenis_pohon');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->json('foto_dokumentasi')->nullable();
                $table->foreignId('perizinan_tebang_pohon_id')->nullable()->constrained('perizinan_tebang_pohons')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_tanam_pohons');
        Schema::dropIfExists('permohonan_pinjam_tamans');
        Schema::dropIfExists('perizinan_tebang_pohons');
    }
};
