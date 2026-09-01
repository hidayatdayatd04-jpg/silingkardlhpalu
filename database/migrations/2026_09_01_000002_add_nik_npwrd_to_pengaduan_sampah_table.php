<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengaduan_sampah') && ! Schema::hasColumn('pengaduan_sampah', 'nik_npwrd')) {
            Schema::table('pengaduan_sampah', function (Blueprint $table) {
                $table->string('nik_npwrd', 50)->nullable()->after('nomor_hp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengaduan_sampah') && Schema::hasColumn('pengaduan_sampah', 'nik_npwrd')) {
            Schema::table('pengaduan_sampah', function (Blueprint $table) {
                $table->dropColumn('nik_npwrd');
            });
        }
    }
};
