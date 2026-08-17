<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan parent_id untuk mendukung layer bertingkat (grouping).
     * Satu layer dapat memiliki sub-layer (child) yang mengacu pada parent-nya.
     */
    public function up(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('gis_data_layer')
                ->nullOnDelete();
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('gis_data_layer', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
