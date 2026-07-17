<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titik_tpas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('kapasitas')->nullable();
            $table->string('foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('titik_tpsts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('kapasitas')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('titik_bank_sampahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('jam_operasional')->nullable();
            $table->string('kontak')->nullable();
            $table->timestamps();
        });

        Schema::create('titik_tps', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('jadwal_armadas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rute');
            $table->string('hari');
            $table->string('jam');
            $table->text('wilayah_dilalui')->nullable();
            $table->timestamps();
        });

        Schema::create('statistik_sampahs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('volume_ton', 10, 2);
            $table->string('periode', 20);
            $table->timestamps();
        });

        // Tabel jenis_lb3s sudah dibuat di migration 2026_07_08_130000_create_master_data_tables.php

        Schema::create('registrasi_usaha_lb3s', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_registrasi')->unique();
            $table->string('nama_perusahaan');
            $table->text('alamat');
            $table->foreignId('jenis_lb3_id')->constrained('jenis_lb3s')->cascadeOnDelete();
            $table->string('status')->default('Diajukan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_rintek_perteks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->foreignId('registrasi_usaha_lb3_id')->nullable()->constrained('registrasi_usaha_lb3s')->nullOnDelete();
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
        Schema::dropIfExists('pengajuan_rintek_perteks');
        Schema::dropIfExists('registrasi_usaha_lb3s');
        // jenis_lb3s akan di-drop di migration 2026_07_08_130000_create_master_data_tables.php
        Schema::dropIfExists('statistik_sampahs');
        Schema::dropIfExists('jadwal_armadas');
        Schema::dropIfExists('titik_tps');
        Schema::dropIfExists('titik_bank_sampahs');
        Schema::dropIfExists('titik_tpsts');
        Schema::dropIfExists('titik_tpas');
    }
};
