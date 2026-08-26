<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pelanggarans') || ! Schema::hasColumn('pelanggarans', 'objek_pengawasan_id')) {
            return;
        }

        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->dropForeign(['objek_pengawasan_id']);
            $table->dropColumn('objek_pengawasan_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pelanggarans') || Schema::hasColumn('pelanggarans', 'objek_pengawasan_id')) {
            return;
        }

        Schema::table('pelanggarans', function (Blueprint $table) {
            // Relasi lama sengaja tidak dipulihkan. Rollback hanya menambah
            // kolom nullable agar skema dapat diputar balik tanpa membuat
            // hubungan objek pengawasan lama muncul kembali.
            $table->unsignedBigInteger('objek_pengawasan_id')->nullable();
        });
    }
};
