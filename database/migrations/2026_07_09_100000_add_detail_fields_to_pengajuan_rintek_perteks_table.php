<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_rintek_perteks', function (Blueprint $table) {
            $table->string('nama_penanggung_jawab')->default('')->after('nama_perusahaan');
            $table->string('nomor_nib')->default('')->after('nama_penanggung_jawab');
            $table->string('npwp', 30)->nullable()->after('nomor_nib');
            $table->string('jenis_usaha')->default('')->after('npwp');
            $table->text('alamat_lengkap')->nullable()->after('jenis_usaha');
            $table->string('nomor_telepon', 20)->default('')->after('alamat_lengkap');
            $table->string('email')->default('')->after('nomor_telepon');
            $table->string('jenis_pengajuan')->default('')->after('email');
            $table->text('keterangan_tambahan')->nullable()->after('sop_tanggap_darurat');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_rintek_perteks', function (Blueprint $table) {
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
