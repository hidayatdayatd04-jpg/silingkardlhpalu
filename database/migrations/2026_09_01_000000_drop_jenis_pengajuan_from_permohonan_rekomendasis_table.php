<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('permohonan_rekomendasis') ? 'permohonan_rekomendasis' : (Schema::hasTable('permohonan_rekomendasi') ? 'permohonan_rekomendasi' : null);

        if ($tableName && Schema::hasColumn($tableName, 'jenis_pengajuan')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('jenis_pengajuan');
            });
        }
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('permohonan_rekomendasis') ? 'permohonan_rekomendasis' : (Schema::hasTable('permohonan_rekomendasi') ? 'permohonan_rekomendasi' : null);

        if ($tableName && ! Schema::hasColumn($tableName, 'jenis_pengajuan')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('jenis_pengajuan')->default('')->after('nomor_telepon');
            });
        }
    }
};
