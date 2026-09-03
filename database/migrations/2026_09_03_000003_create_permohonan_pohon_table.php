<?php

use App\Enums\JenisTindakanPohon;
use App\Enums\StatusPermohonanPohon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_pohon', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 50)->unique()->index();
            $table->string('nama_pelapor', 255);
            $table->string('nomor_hp', 50)->index();
            $table->string('jenis_tindakan', 50)->default(JenisTindakanPohon::PEMANGKASAN->value);
            $table->text('lokasi_pohon');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('jenis_pohon', 255)->nullable();
            $table->text('alasan_pengajuan');
            $table->string('foto_pohon', 500)->nullable();
            $table->text('keterangan_tambahan')->nullable();

            // Status alur kerja: Diajukan -> Verifikasi -> Survei Lapangan -> Disetujui/Ditolak -> Dijadwalkan -> Proses Eksekusi -> Selesai
            $table->string('status', 50)->default(StatusPermohonanPohon::DIAJUKAN->value)->index();

            // Verifikasi & Survei Lapangan
            $table->text('catatan_verifikasi')->nullable();
            $table->date('tanggal_survei')->nullable();
            $table->string('petugas_survei', 255)->nullable();
            $table->text('kondisi_pohon')->nullable();
            $table->text('rekomendasi_tindakan')->nullable();
            $table->text('catatan_survei')->nullable();
            $table->text('alasan_penolakan')->nullable();

            // Pelaksanaan & Dokumentasi
            $table->date('tanggal_pelaksanaan')->nullable();
            $table->string('tim_pelaksana', 255)->nullable();
            $table->text('catatan_pelaksanaan')->nullable();
            $table->json('foto_sebelum')->nullable();
            $table->json('foto_sesudah')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_pohon');
    }
};
