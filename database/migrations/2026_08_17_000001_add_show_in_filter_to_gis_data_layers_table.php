<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->boolean('show_in_filter')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->dropColumn('show_in_filter');
        });
    }
};
