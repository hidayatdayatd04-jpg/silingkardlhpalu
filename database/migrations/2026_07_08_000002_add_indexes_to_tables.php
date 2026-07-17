<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambahkan indeks database yang hilang.
     */
    public function up(): void
    {
        Schema::table('ikm_responses', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('laporans', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('ikm_responses', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('laporans', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
