<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pelanggarans') || Schema::hasColumn('pelanggarans', 'sidak_manual')) {
            return;
        }

        Schema::table('pelanggarans', function (Blueprint $table) {
            // Jangan gunakan ->after(): PostgreSQL (Railway) tidak mendukung
            // pengaturan urutan kolom seperti MySQL/XAMPP.
            $table->text('sidak_manual')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pelanggarans') || ! Schema::hasColumn('pelanggarans', 'sidak_manual')) {
            return;
        }

        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->dropColumn('sidak_manual');
        });
    }
};
