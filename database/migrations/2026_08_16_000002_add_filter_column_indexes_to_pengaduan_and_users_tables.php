<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Task 11 — Tambah index untuk kolom filter tambahan (non-status).
 *
 * `AdminRegistry::buildFilters()` menghasilkan query filter pada kolom:
 *   - laporans ............................... WHERE jenis_pengaduan IN (...)
 *   - pengaduan_tata_penataans ............... WHERE jenis_pengaduan IN (...)
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
        $this->addIndex('laporans', 'jenis_pengaduan');
        $this->addIndex('pengaduan_tata_penataans', 'jenis_pengaduan');
        $this->addIndex('sosialisasis', 'jenis_kegiatan');
        $this->addIndex('sosialisasis', 'tanggal');
        $this->addIndex('users', 'is_active');
    }

    public function down(): void
    {
        $this->dropIndex('laporans', 'jenis_pengaduan');
        $this->dropIndex('pengaduan_tata_penataans', 'jenis_pengaduan');
        $this->dropIndex('sosialisasis', 'jenis_kegiatan');
        $this->dropIndex('sosialisasis', 'tanggal');
        $this->dropIndex('users', 'is_active');
    }
};