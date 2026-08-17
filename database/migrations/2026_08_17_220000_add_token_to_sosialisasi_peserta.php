<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tambah kolom token acak untuk akses sertifikat sosialisasi.
     *
     * Sebelumnya URL sertifikat memakai ID peserta yang mudah ditebak
     * (IDOR). Token acak 32 karakter menggantikan ID sebagai kunci akses.
     * Baris lama di-backfill agar sertifikat yang sudah terbit tetap
     * bisa diakses lewat token barunya.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sosialisasi_peserta')) {
            return;
        }

        if (! Schema::hasColumn('sosialisasi_peserta', 'token')) {
            Schema::table('sosialisasi_peserta', function (Blueprint $table) {
                $table->string('token', 64)->nullable()->unique();
            });
        }

        // Backfill baris existing dengan token acak.
        DB::table('sosialisasi_peserta')
            ->whereNull('token')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($row) {
                DB::table('sosialisasi_peserta')
                    ->where('id', $row->id)
                    ->update(['token' => Str::random(32)]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sosialisasi_peserta') && Schema::hasColumn('sosialisasi_peserta', 'token')) {
            Schema::table('sosialisasi_peserta', function (Blueprint $table) {
                $table->dropColumn('token');
            });
        }
    }
};
