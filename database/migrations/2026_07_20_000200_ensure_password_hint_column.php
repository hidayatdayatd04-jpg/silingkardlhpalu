<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom password_hint menyimpan password secara plaintext sebagai
     * "petunjuk" yang ditampilkan di halaman detail pengguna admin.
     * Dibuat idempoten agar aman dijalankan berulang kali.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'password_hint')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password_hint')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'password_hint')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_hint');
            });
        }
    }
};
