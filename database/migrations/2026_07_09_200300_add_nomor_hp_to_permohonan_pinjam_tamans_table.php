<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_pinjam_taman', function (Blueprint $table) {
            $table->string('nomor_hp')->nullable();
            $table->string('email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_pinjam_taman', function (Blueprint $table) {
            $table->dropColumn(['nomor_hp', 'email']);
        });
    }
};
