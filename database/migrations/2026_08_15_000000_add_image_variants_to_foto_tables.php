<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['laporan_fotos', 'pengaduan_tata_penataan_fotos'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Three optimised variants replace the old single path_foto.
                $table->text('thumb_path')->nullable()->after('path_foto');
                $table->text('medium_path')->nullable()->after('thumb_path');
                $table->text('full_path')->nullable()->after('medium_path');

                // Async processing status: pending | processing | done | failed
                $table->string('status')->default('pending')->after('full_path');
                $table->text('error_message')->nullable()->after('status');
                $table->string('staging_path')->nullable()->after('error_message');
            });
        }
    }

    public function down(): void
    {
        $tables = ['laporan_fotos', 'pengaduan_tata_penataan_fotos'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['thumb_path', 'medium_path', 'full_path', 'status', 'error_message', 'staging_path']);
            });
        }
    }
};
