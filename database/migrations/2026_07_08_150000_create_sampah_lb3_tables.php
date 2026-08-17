<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_armada', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rute');
            $table->string('hari');
            $table->string('jam');
            $table->text('wilayah_dilalui')->nullable();
            $table->timestamps();
        });

        Schema::create('statistik_sampah', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('volume_ton', 10, 2);
            $table->string('periode', 20);
            $table->timestamps();
        });

        Schema::create('registrasi_usaha_lb3', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_registrasi')->unique();
            $table->string('nama_perusahaan');
            $table->text('alamat');
            $table->string('jenis_lb3')->nullable();
            $table->string('status')->default('Diajukan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_rintek_pertek', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->foreignId('registrasi_usaha_lb3_id')->nullable()->constrained('registrasi_usaha_lb3')->nullOnDelete();
            $table->string('nama_perusahaan');
            $table->string('surat_permohonan');
            $table->string('dplh_ukl_upl');
            $table->string('nib');
            $table->string('sppl');
            $table->string('denah_tps_lb3');
            $table->string('sop_tanggap_darurat');
            $table->string('status')->default('Diajukan');
            $table->text('catatan_verifikasi')->nullable();
            $table->json('verifikasi_dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_rintek_pertek');
        Schema::dropIfExists('registrasi_usaha_lb3');
        Schema::dropIfExists('statistik_sampah');
        Schema::dropIfExists('jadwal_armada');
    }
};
