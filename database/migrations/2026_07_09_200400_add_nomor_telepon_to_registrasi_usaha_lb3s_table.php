<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
            $table->string('nomor_telepon')->nullable()->after('nama_perusahaan');
            $table->string('email')->nullable()->after('nomor_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_usaha_lb3s', function (Blueprint $table) {
            $table->dropColumn(['nomor_telepon', 'email']);
        });
    }
};
