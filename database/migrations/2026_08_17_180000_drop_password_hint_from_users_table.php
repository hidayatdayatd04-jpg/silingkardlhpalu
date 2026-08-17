<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom password_hint menyimpan password plaintext sebagai "petunjuk" —
     * temuan kritis audit keamanan. Kolom dihapus permanen; password hanya
     * boleh tersimpan sebagai hash.
     */
    public function up(): void
    {
        if (Schema::hasColumn('user', 'password_hint')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('password_hint');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('user', 'password_hint')) {
            Schema::table('user', function (Blueprint $table) {
                $table->string('password_hint')->nullable();
            });
        }
    }
};
