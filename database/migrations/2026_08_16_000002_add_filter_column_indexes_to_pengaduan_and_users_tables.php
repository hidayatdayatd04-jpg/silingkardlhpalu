<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Task 11 — Tambah index untuk kolom filter tambahan (non-status).
 *
 * `AdminRegistry::buildFilters()` menghasilkan query filter pada kolom:
 *   - tabel pengaduan (pengaduan_pengendalian, pengaduan_sampah, pengaduan_rth,
 *     pengaduan_tata_penataan) ............... WHERE jenis_pengaduan IN (...)
 *     — index sudah dibuat di migration 2026_08_17_100000_restructure_pengaduan_tables.php
 *   - sosialisis ............................. WHERE jenis_kegiatan = ...  & daterange tanggal
 *   - users .................................. WHERE is_active = ...  (Dashboard.activeUsers)
 *
 * Kolom status & created_at sudah masuk migration Task 2.
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
        $cols = (array) $columns;
        $name = $table.'_'.implode('_', $cols).'_index';

        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn (Blueprint $b) => $b->dropIndex($name));
    }

    public function up(): void
    {
        $this->addIndex('sosialisasi', 'jenis_kegiatan');
        $this->addIndex('sosialisasi', 'tanggal');
        $this->addIndex('user', 'is_active');
    }

    public function down(): void
    {
        $this->dropIndex('sosialisasi', 'jenis_kegiatan');
        $this->dropIndex('sosialisasi', 'tanggal');
        $this->dropIndex('user', 'is_active');
    }
};