<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permohonan_pinjam_taman') && Schema::hasColumn('permohonan_pinjam_taman', 'surat_jaminan')) {
            Schema::table('permohonan_pinjam_taman', function (Blueprint $table) {
                $table->dropColumn('surat_jaminan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permohonan_pinjam_taman') && ! Schema::hasColumn('permohonan_pinjam_taman', 'surat_jaminan')) {
            Schema::table('permohonan_pinjam_taman', function (Blueprint $table) {
                $table->string('surat_jaminan')->nullable()->after('jaminan_kebersihan');
            });
        }
    }
};
