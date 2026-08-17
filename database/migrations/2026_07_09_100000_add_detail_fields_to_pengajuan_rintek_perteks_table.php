<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_rintek_pertek', function (Blueprint $table) {
            $table->string('nama_penanggung_jawab')->default('');
            $table->string('nomor_nib')->default('');
            $table->string('npwp', 30)->nullable();
            $table->string('jenis_usaha')->default('');
            $table->text('alamat_lengkap')->nullable();
            $table->string('nomor_telepon', 20)->default('');
            $table->string('email')->default('');
            $table->string('jenis_pengajuan')->default('');
            $table->text('keterangan_tambahan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_rintek_pertek', function (Blueprint $table) {
            $table->dropColumn([
                'nama_penanggung_jawab',
                'nomor_nib',
                'npwp',
                'jenis_usaha',
                'alamat_lengkap',
                'nomor_telepon',
                'email',
                'jenis_pengajuan',
                'keterangan_tambahan',
            ]);
        });
    }
};
