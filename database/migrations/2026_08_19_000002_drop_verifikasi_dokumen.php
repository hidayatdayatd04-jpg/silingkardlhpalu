<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pengajuan_rintek_pertek', 'verifikasi_dokumen')) {
            Schema::table('pengajuan_rintek_pertek', function (Blueprint $table): void {
                $table->dropColumn('verifikasi_dokumen');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pengajuan_rintek_pertek', 'verifikasi_dokumen')) {
            Schema::table('pengajuan_rintek_pertek', function (Blueprint $table): void {
                $table->json('verifikasi_dokumen')->nullable();
            });
        }
    }
};
