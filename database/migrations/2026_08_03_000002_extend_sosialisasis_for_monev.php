<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sosialisasis', function (Blueprint $table) {
            // Kolom ini sempat terpasang di MySQL oleh migrasi parsial yang gagal,
            // jadi tambah hanya bila belum ada (aman untuk refresh SQLite).
            if (! Schema::hasColumn('sosialisasis', 'jenis_kegiatan')) {
                $table->string('jenis_kegiatan')->default('sosialisasi');
            }
            if (! Schema::hasColumn('sosialisasis', 'periode_tw')) {
                $table->string('periode_tw')->nullable();
            }
            if (! Schema::hasColumn('sosialisasis', 'tahun')) {
                $table->string('tahun')->nullable();
            }

            $table->date('tanggal')->nullable()->change();
            $table->text('materi')->nullable()->change();
        });

        Schema::table('sosialisasi_pesertas', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            // Lepas FK & indeks unik sebelum struktur diubah & dibangun ulang.
            // MySQL: drop berdasarkan nama (FK objek memang tidak ada di MySQL).
            // SQLite: drop berdasarkan kolom (dipecahkan saat rebuild tabel).
            if ($driver === 'mysql') {
                try { $table->dropForeign('sosialisasi_pesertas_sosialisasi_id_foreign'); } catch (\Throwable $e) {}
                try { $table->dropForeign('sosialisasi_pesertas_objek_pengawasan_id_foreign'); } catch (\Throwable $e) {}
            } else {
                $table->dropForeign(['sosialisasi_id']);
                $table->dropForeign(['objek_pengawasan_id']);
            }

            $table->dropUnique('sosialisasi_peserta_unique');

            $table->foreignId('objek_pengawasan_id')->nullable()->change();

            $table->string('nama_perusahaan')->nullable();
            $table->string('jenis_usaha')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('tim_survey')->nullable();

            $table->foreign('sosialisasi_id')
                ->references('id')
                ->on('sosialisasis')
                ->cascadeOnDelete();
            $table->foreign('objek_pengawasan_id')
                ->references('id')
                ->on('objek_pengawasans')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sosialisasi_pesertas', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                $table->dropForeign('sosialisasi_pesertas_sosialisasi_id_foreign');
                $table->dropForeign('sosialisasi_pesertas_objek_pengawasan_id_foreign');
            } else {
                $table->dropForeign(['sosialisasi_id']);
                $table->dropForeign(['objek_pengawasan_id']);
            }

            $table->dropColumn(['nama_perusahaan', 'jenis_usaha', 'tanggal', 'lokasi', 'tim_survey']);

            $table->foreignId('objek_pengawasan_id')->nullable(false)->change();
            $table->unique(['sosialisasi_id', 'objek_pengawasan_id'], 'sosialisasi_peserta_unique');

            $table->foreign('sosialisasi_id')
                ->references('id')
                ->on('sosialisasis')
                ->cascadeOnDelete();
            $table->foreign('objek_pengawasan_id')
                ->references('id')
                ->on('objek_pengawasans')
                ->cascadeOnDelete();
        });

        Schema::table('sosialisasis', function (Blueprint $table) {
            $table->dropColumn(['jenis_kegiatan', 'periode_tw', 'tahun']);
        });
    }
};
