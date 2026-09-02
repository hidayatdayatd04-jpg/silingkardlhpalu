<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->string('tampilkan_di', 50)->nullable()->default('jalur-angkut')->after('bidang');
        });
    }

    public function down(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->dropColumn('tampilkan_di');
        });
    }
};
