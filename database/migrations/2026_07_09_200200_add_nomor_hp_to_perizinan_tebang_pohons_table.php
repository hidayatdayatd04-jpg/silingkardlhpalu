<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perizinan_tebang_pohons', function (Blueprint $table) {
            $table->string('nomor_hp')->nullable()->after('nama_pemohon');
            $table->string('email')->nullable()->after('nomor_hp');
        });
    }

    public function down(): void
    {
        Schema::table('perizinan_tebang_pohons', function (Blueprint $table) {
            $table->dropColumn(['nomor_hp', 'email']);
        });
    }
};
