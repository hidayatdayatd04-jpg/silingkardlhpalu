<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_tpu', function (Blueprint $table) {
            $table->json('foto_dokumentasi')->nullable()->after('kapasitas_blok');
        });
    }

    public function down(): void
    {
        Schema::table('data_tpu', function (Blueprint $table) {
            $table->dropColumn('foto_dokumentasi');
        });
    }
};
