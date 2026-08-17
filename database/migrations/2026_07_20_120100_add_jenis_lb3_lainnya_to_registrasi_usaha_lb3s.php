<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_usaha_lb3', function (Blueprint $table) {
            $table->string('jenis_lb3_lainnya')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_usaha_lb3', function (Blueprint $table) {
            $table->dropColumn('jenis_lb3_lainnya');
        });
    }
};
