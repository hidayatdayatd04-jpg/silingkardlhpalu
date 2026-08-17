<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Task 2 — Tambah index database untuk query agregat dashboard & filter tabel pengaduan.
 *
 * Index status/created_at/jenis_pengaduan untuk tabel pengaduan baru
 * (pengaduan_pengendalian, pengaduan_sampah, pengaduan_rth, pengaduan_tata_penataan)
 * sudah dibuat langsung di migration 2026_08_17_100000_restructure_pengaduan_tables.php.
 *
 * Migration ini menambahkan index untuk tabel pendukung lainnya.
 */
return new class extends Migration
{
    protected function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('tablename', $table)
                ->where('indexname', $index)
                ->exists();
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::raw('database()'))
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        }

        return Schema::hasIndex($table, $index);
    }

    protected function addIndex(string $table, array|string $columns): void
    {
        $cols = (array) $columns;
        $name = $table.'_'.implode('_', $cols).'_index';

        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn (Blueprint $b) => $b->index($cols, $name));
    }

    protected function dropIndex(string $table, array|string $columns): void
    {
        $cols   = (array) $columns;
        $name   = $table.'_'.implode('_', $cols).'_index';

        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn (Blueprint $b) => $b->dropIndex($name));
    }

    public function up(): void
    {
        // ── registrasi_usaha_lb3 ──
        $this->addIndex('registrasi_usaha_lb3', 'status');
        $this->addIndex('registrasi_usaha_lb3', 'created_at');

        // ── permohonan_rekomendasis ──
        $this->addIndex('permohonan_rekomendasis', 'status');
        $this->addIndex('permohonan_rekomendasis', 'created_at');

        // ── pengajuan_rintek_pertek ──
        $this->addIndex('pengajuan_rintek_pertek', 'status');
        $this->addIndex('pengajuan_rintek_pertek', 'created_at');

        // ── permohonan_pinjam_taman ──
        $this->addIndex('permohonan_pinjam_taman', 'status');
        $this->addIndex('permohonan_pinjam_taman', 'created_at');

        // ── data_tanam_pohon ──
        $this->addIndex('data_tanam_pohon', 'created_at');

        // ── sosialisasi ──
        $this->addIndex('sosialisasi', 'created_at');

        // ── pelanggarans (tanpa kolom status; index created_at) ──
        $this->addIndex('pelanggarans', 'created_at');

        // ── sidak (filter status_tindak_lanjut + created_at) ──
        $this->addIndex('sidak', 'status_tindak_lanjut');
        $this->addIndex('sidak', 'created_at');
    }

    public function down(): void
    {
        $this->dropIndex('registrasi_usaha_lb3', 'status');
        $this->dropIndex('registrasi_usaha_lb3', 'created_at');

        $this->dropIndex('permohonan_rekomendasis', 'status');
        $this->dropIndex('permohonan_rekomendasis', 'created_at');

        $this->dropIndex('pengajuan_rintek_pertek', 'status');
        $this->dropIndex('pengajuan_rintek_pertek', 'created_at');

        $this->dropIndex('permohonan_pinjam_taman', 'status');
        $this->dropIndex('permohonan_pinjam_taman', 'created_at');

        $this->dropIndex('data_tanam_pohon', 'created_at');
        $this->dropIndex('sosialisasi', 'created_at');
        $this->dropIndex('pelanggarans', 'created_at');
        $this->dropIndex('sidak', 'status_tindak_lanjut');
        $this->dropIndex('sidak', 'created_at');
    }
};