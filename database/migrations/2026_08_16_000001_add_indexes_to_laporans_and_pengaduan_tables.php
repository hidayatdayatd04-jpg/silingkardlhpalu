<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Task 2 — Tambah index database untuk query agregat dashboard & filter tabel pengaduan.
 *
 * Query `DashboardController::aggregateLaporan()` melakukan GROUP BY bidang/status
 * dan bidang/created_at di tabel `laporans` setiap cache dashboard kedaluwarsa.
 * Kolom `bidang` ditambahkan oleh migration 2026_07_08_140100_generalize_laporans_table.php
 * tanpa index — sehingga agregasi berjalan full table scan (Seq Scan).
 *
 * Migration ini menambahkan:
 *  - laporans        : index bidang, (bidang,status), (bidang,created_at), status, created_at
 *  - semua tabel pengaduan lain : index status + created_at (sepadan dengan filter
 *    `WHERE status=...` / `ORDER BY created_at` di ResourceController & AdminRegistry).
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
        // ── laporans ── tabel unifikasi pengaduan pengendalian/sampah/rth ───────────
        $this->addIndex('laporans', 'bidang');
        $this->addIndex('laporans', ['bidang', 'status']);
        $this->addIndex('laporans', ['bidang', 'created_at']);
        $this->addIndex('laporans', 'status');
        $this->addIndex('laporans', 'created_at');

        // ── pengaduan_tata_penataans ──
        $this->addIndex('pengaduan_tata_penataans', 'status');
        $this->addIndex('pengaduan_tata_penataans', 'created_at');

        // ── registrasi_usaha_lb3s ──
        $this->addIndex('registrasi_usaha_lb3s', 'status');
        $this->addIndex('registrasi_usaha_lb3s', 'created_at');

        // ── permohonan_rekomendasis ──
        $this->addIndex('permohonan_rekomendasis', 'status');
        $this->addIndex('permohonan_rekomendasis', 'created_at');

        // ── pengajuan_rintek_perteks ──
        $this->addIndex('pengajuan_rintek_perteks', 'status');
        $this->addIndex('pengajuan_rintek_perteks', 'created_at');

        // ── permohonan_pinjam_tamans ──
        $this->addIndex('permohonan_pinjam_tamans', 'status');
        $this->addIndex('permohonan_pinjam_tamans', 'created_at');

        // ── data_tanam_pohons ──
        $this->addIndex('data_tanam_pohons', 'created_at');

        // ── sosialisis ──
        $this->addIndex('sosialisasis', 'created_at');

        // ── pelanggarans (tanpa kolom status; index created_at) ──
        $this->addIndex('pelanggarans', 'created_at');

        // ── sidaks (filter status_tindak_lanjut + created_at) ──
        $this->addIndex('sidaks', 'status_tindak_lanjut');
        $this->addIndex('sidaks', 'created_at');
    }

    public function down(): void
    {
        $this->dropIndex('laporans', 'bidang');
        $this->dropIndex('laporans', ['bidang', 'status']);
        $this->dropIndex('laporans', ['bidang', 'created_at']);
        $this->dropIndex('laporans', 'status');
        $this->dropIndex('laporans', 'created_at');

        $this->dropIndex('pengaduan_tata_penataans', 'status');
        $this->dropIndex('pengaduan_tata_penataans', 'created_at');

        $this->dropIndex('registrasi_usaha_lb3s', 'status');
        $this->dropIndex('registrasi_usaha_lb3s', 'created_at');

        $this->dropIndex('permohonan_rekomendasis', 'status');
        $this->dropIndex('permohonan_rekomendasis', 'created_at');

        $this->dropIndex('pengajuan_rintek_perteks', 'status');
        $this->dropIndex('pengajuan_rintek_perteks', 'created_at');

        $this->dropIndex('permohonan_pinjam_tamans', 'status');
        $this->dropIndex('permohonan_pinjam_tamans', 'created_at');

        $this->dropIndex('data_tanam_pohons', 'created_at');
        $this->dropIndex('sosialisasis', 'created_at');
        $this->dropIndex('pelanggarans', 'created_at');
        $this->dropIndex('sidaks', 'status_tindak_lanjut');
        $this->dropIndex('sidaks', 'created_at');
    }
};