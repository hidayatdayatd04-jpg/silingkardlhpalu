<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_rekomendasis', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonan_rekomendasis', 'jenis_usaha')) {
                $table->string('jenis_usaha')->nullable()->after('npwp');
            }
        });

        DB::table('permohonan_rekomendasis')
            ->whereNull('jenis_usaha')
            ->whereNotNull('jenis_usaha_id')
            ->update([
                'jenis_usaha' => DB::raw('(select nama from jenis_usahas where jenis_usahas.id = permohonan_rekomendasis.jenis_usaha_id limit 1)'),
            ]);

        try {
            Schema::table('permohonan_rekomendasis', function (Blueprint $table) {
                $table->dropForeign(['jenis_usaha_id']);
            });
        } catch (Throwable) {
            //
        }

        try {
            Schema::table('permohonan_rekomendasis', function (Blueprint $table) {
                $table->foreignId('jenis_usaha_id')->nullable()->change();
                $table->foreign('jenis_usaha_id')->references('id')->on('jenis_usahas')->nullOnDelete();
            });
        } catch (Throwable) {
            //
        }
    }

    public function down(): void
    {
        Schema::table('permohonan_rekomendasis', function (Blueprint $table) {
            if (Schema::hasColumn('permohonan_rekomendasis', 'jenis_usaha')) {
                $table->dropColumn('jenis_usaha');
            }
        });
    }
};
