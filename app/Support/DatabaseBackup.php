<?php

namespace App\Support;

use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use ZipArchive;

/**
 * Backup & restore database (Neon PostgreSQL) + file storage (Backblaze B2).
 *
 * dump()    → buat ZIP berisi database.sql + meta.json + seluruh file di disk 'public' (B2),
 *             lalu simpan ZIP ke disk backup (default: B2, prefix "backups/").
 * restore() → merge non-destruktif: file storage dari backup ditulis/ditimpa ke disk 'public'
 *             (file di luar backup tetap dipertahankan), lalu database.sql dieksekusi sebagai
 *             merge — baris di backup dipulihkan/diperbarui, baris di luar backup tetap ada.
 */
class DatabaseBackup
{
    /** Prefix folder file backup pada disk backup. */
    public const DIR = 'backups';

    /** Disk tempat file aplikasi (foto/dokumen/shp) yang ikut dibackup. */
    public const FILE_DISK = 'public';

    protected PDO $pdo;

    /** Callback progress opsional: fn(int $percent, string $label). */
    protected $onProgress = null;

    /** Callback pembatalan opsional: fn(): bool — dicek kooperatif di loop. */
    protected $isCancelled = null;

    /** Cache kolom primary key per tabel (untuk upsert merge), reset tiap restore. */
    protected array $pkCache = [];

    /** Nama constraint yang sudah ada di skema public — di-load sekali per restore. */
    protected array $existingConstraints = [];

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Pasang callback progress & pembatalan (dipakai job latar belakang).
     * Tanpa callback, dump()/restore() bekerja sinkron seperti sebelumnya.
     */
    public function withProgress(?callable $onProgress, ?callable $isCancelled = null): static
    {
        $this->onProgress = $onProgress;
        $this->isCancelled = $isCancelled;

        return $this;
    }

    protected function reportProgress(int $percent, string $label): void
    {
        if ($this->onProgress !== null) {
            ($this->onProgress)(max(0, min(100, $percent)), $label);
        }
    }

    /**
     * @throws BackupCancelledException bila pengguna meminta pembatalan
     */
    protected function checkCancelled(): void
    {
        if ($this->isCancelled !== null && ($this->isCancelled)()) {
            throw new BackupCancelledException;
        }
    }

    /**
     * Closure progress yang memetakan persen internal (0–100) ke rentang
     * $from–$to pada callback utama, dengan throttle agar tidak membanjiri cache.
     */
    protected function subProgress(int $from, int $to): \Closure
    {
        $lastPercent = -1;
        $lastWrite = 0.0;

        return function (int $percent, string $label) use ($from, $to, &$lastPercent, &$lastWrite): void {
            $mapped = $from + (int) round(($to - $from) * max(0, min(100, $percent)) / 100);

            $now = microtime(true);
            if ($mapped === $lastPercent && ($now - $lastWrite) < 1.0) {
                return;
            }

            $lastPercent = $mapped;
            $lastWrite = $now;

            $this->reportProgress($mapped, $label);
        };
    }

    /**
     * Disk tujuan penyimpanan file backup ZIP (default: Backblaze B2).
     */
    public static function diskName(): string
    {
        return (string) config('filesystems.backup_disk', 'b2');
    }

    /**
     * Buat backup lengkap (database + seluruh file storage) → path relatif di disk backup.
     */
    public function dump(?string $filename = null): string
    {
        @set_time_limit(900);

        $this->checkCancelled();
        $this->reportProgress(2, 'Menyiapkan cadangan…');

        $filename ??= 'backup-'.now()->format('Ymd-His').'.zip';
        $relative = self::DIR.'/'.$filename;

        $zipPath = tempnam(sys_get_temp_dir(), 'dlhbackup_');
        @unlink($zipPath);
        $zipPath .= '.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file cadangan.');
        }

        $stats = ['tables' => 0, 'rows' => 0];

        // 1) Dump database → database.sql
        $zip->addFromString('database.sql', $this->generateSqlDump($stats, $this->subProgress(5, 55)));

        // 2) Seluruh file dari disk penyimpanan aplikasi — foto, dokumen, shp, dll.
        $fileStats = $this->addStorageFiles($zip, $this->subProgress(55, 85));

        // 3) Metadata backup
        $zip->addFromString('meta.json', json_encode([
            'app' => (string) config('app.name'),
            'url' => (string) config('app.url'),
            'driver' => DB::connection()->getDriverName(),
            'database' => DB::connection()->getDatabaseName(),
            'created_at' => now()->toIso8601String(),
            'tables' => $stats['tables'],
            'rows' => $stats['rows'],
            'files' => $fileStats['files'],
            'file_bytes' => $fileStats['bytes'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->close();

        // 4) Upload ZIP ke disk backup via stream
        $this->checkCancelled();
        $this->reportProgress(88, 'Menyimpan cadangan…');

        $stream = @fopen($zipPath, 'r');
        if (! $stream) {
            throw new \RuntimeException('Gagal membaca file cadangan.');
        }

        try {
            Storage::disk(self::diskName())->put($relative, $stream);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal menyimpan cadangan.', 0, $e);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($zipPath);
        }

        $this->deleteOldBackups($relative);

        $this->reportProgress(100, 'Cadangan berhasil dibuat');

        return $relative;
    }

    /**
     * Restore dari file backup lokal (.zip atau .sql). Return jumlah statement dieksekusi.
     */
    public function restore(string $fullPath): int
    {
        if (! is_file($fullPath)) {
            throw new \RuntimeException('File backup tidak ditemukan.');
        }

        $this->checkCancelled();

        if (preg_match('/\.zip$/i', $fullPath)) {
            return $this->restoreZip($fullPath);
        }

        return $this->restoreSqlFile($fullPath);
    }

    /**
     * Restore dari file .zip (database + file storage).
     */
    protected function restoreZip(string $zipPath): int
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Gagal membuka file ZIP backup.');
        }

        $this->checkCancelled();
        $this->reportProgress(1, 'Membuka cadangan…');

        // 1) Tulis file storage (B2) dari ZIP — file yang ada di backup ditimpa,
        //    file lain yang tidak ada di backup tetap dipertahankan (non-destruktif).
        $total = $zip->numFiles;
        for ($i = 0; $i < $total; $i++) {
            $this->checkCancelled();

            $name = $zip->getNameIndex($i);
            if ($name === null || str_ends_with($name, '/') || $name === 'database.sql' || $name === 'meta.json') {
                continue;
            }

            $name = str_replace('\\', '/', $name);

            if (! str_starts_with($name, self::FILE_DISK_DIR_PREFIX())) {
                continue;
            }

            $relative = substr($name, strlen(self::FILE_DISK_DIR_PREFIX()));
            if ($relative === '') {
                continue;
            }

            // Guard zip-slip: tolak entri berisi traversal direktori ('..')
            // atau path absolut agar restore tidak menulis di luar disk.
            if (str_starts_with($relative, '/') || in_array('..', explode('/', $relative), true)) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            $disk = Storage::disk(self::FILE_DISK);

            // Guard defensif: bila file sudah ada dan isinya identik, lewati
            // penulisan. Selain lebih cepat, ini mencegah terciptanya versi
            // objek baru yang mubazir di B2 (versioning aktif) dan menutup
            // kemungkinan file membengkak akibat penulisan berulang.
            if ($disk->exists($relative) && $disk->get($relative) === $content) {
                $this->reportProgress((int) round(($i + 1) / max(1, $total) * 55), 'Dokumen '.($i + 1).'/'.$total.' sudah sesuai');

                continue;
            }

            $disk->put($relative, $content);

            $this->reportProgress((int) round(($i + 1) / max(1, $total) * 55), 'Memulihkan dokumen '.($i + 1).'/'.$total);
        }

        // 2) Restore database dari database.sql (merge, non-destruktif).
        $sqlContent = $zip->getFromName('database.sql');
        $zip->close();

        if ($sqlContent === false || trim($sqlContent) === '') {
            throw new \RuntimeException('File database.sql tidak ditemukan atau kosong dalam backup.');
        }

        $this->reportProgress(60, 'Memulihkan data…');

        return $this->executeSql($sqlContent, $this->subProgress(60, 100));
    }

    /**
     * Restore dari file .sql biasa (tanpa file storage).
     */
    protected function restoreSqlFile(string $fullPath): int
    {
        $this->checkCancelled();
        $this->reportProgress(2, 'Membuka file cadangan…');

        $sql = file_get_contents($fullPath);

        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('File backup kosong.');
        }

        return $this->executeSql($sql, $this->subProgress(5, 100));
    }

    /**
     * Prefix path file storage di dalam ZIP (mis. "storage/public/").
     */
    public static function FILE_DISK_DIR_PREFIX(): string
    {
        return 'storage/'.self::FILE_DISK.'/';
    }

    /**
     * Buat dump SQL lengkap ke string.
     *
     * @param  array{tables:int,rows:int}  $stats  diisi statistik selama dump
     * @param  callable|null  $progress  fn(int $percent, string $label)
     */
    protected function generateSqlDump(array &$stats, ?callable $progress = null): string
    {
        $handle = fopen('php://temp/maxmemory:67108864', 'w+');
        if ($handle === false) {
            throw new \RuntimeException('Gagal membuat buffer dump SQL.');
        }

        $driver = DB::connection()->getDriverName();

        fwrite($handle, "-- DLH Palu Database Backup\n");
        fwrite($handle, '-- Generated: '.now()->toDateTimeString()."\n");
        fwrite($handle, '-- Driver: '.$driver."\n");
        fwrite($handle, '-- Database: '.DB::connection()->getDatabaseName()."\n\n");

        if ($driver === 'pgsql') {
            $this->dumpPostgres($handle, $stats, $progress);
        } elseif ($driver === 'sqlite') {
            $this->dumpSqlite($handle);
        } else {
            $this->dumpMysql($handle);
        }

        rewind($handle);
        $sql = stream_get_contents($handle);
        fclose($handle);

        if ($sql === false) {
            throw new \RuntimeException('Gagal membaca buffer dump SQL.');
        }

        return $sql;
    }

    /**
     * Daftarkan seluruh file dari disk 'public' (B2) ke dalam ZIP.
     * Folder backup ("backups/") dilewati agar tidak terjadi backup berantai.
     *
     * @param  callable|null  $progress  fn(int $percent, string $label)
     * @return array{files:int,bytes:int}
     */
    protected function addStorageFiles(ZipArchive $zip, ?callable $progress = null): array
    {
        $disk = Storage::disk(self::FILE_DISK);
        $files = 0;
        $bytes = 0;

        $this->checkCancelled();
        if ($progress) {
            $progress(0, 'Mengumpulkan dokumen…');
        }

        $allFiles = array_values(array_filter(
            array_map(
                fn ($f) => str_replace('\\', '/', $f),
                $disk->allFiles()
            ),
            fn ($f) => ! str_starts_with($f, self::DIR.'/')
        ));

        $total = count($allFiles);
        foreach ($allFiles as $i => $file) {
            $this->checkCancelled();

            try {
                $content = $disk->get($file);
            } catch (\Throwable $e) {
                continue;
            }

            if ($content === null) {
                continue;
            }

            $zip->addFromString(self::FILE_DISK_DIR_PREFIX().$file, $content);
            $files++;
            $bytes += strlen($content);

            if ($progress) {
                $progress((int) round(($i + 1) / max(1, $total) * 100), 'Menyimpan file '.($i + 1).'/'.$total);
            }
        }

        return ['files' => $files, 'bytes' => $bytes];
    }

    /**
     * Hapus path dari disk sekaligus purge semua versi lama di B2.
     *
     * Backblaze B2 mengaktifkan versioning secara default: delete via S3 API
     * hanya menambah hide marker sehingga file lama masih muncul di console B2
     * (walau sudah tidak bisa diunduh). Pemusnahan versi lama ditangani secara
     * terpusat oleh FileUploadService agar tidak perlu duplikasi logika.
     */
    protected static function deleteFromDisk(string $disk, string $path): void
    {
        app(FileUploadService::class)->deletePath($path, $disk);
    }

    /**
     * Hapus semua backup lama setelah backup baru berhasil diunggah —
     * hanya backup terbaru ($exceptRelative) yang dipertahankan.
     */
    protected function deleteOldBackups(string $exceptRelative): void
    {
        $disk = self::diskName();

        try {
            foreach (Storage::disk($disk)->files(self::DIR) as $file) {
                if (! (str_ends_with($file, '.zip') || str_ends_with($file, '.sql'))) {
                    continue;
                }
                if ($file === $exceptRelative) {
                    continue;
                }

                self::deleteFromDisk($disk, $file);
            }
        } catch (\Throwable $e) {
            // Pembersihan bersifat best-effort; jangan gagalkan backup utama.
        }
    }

    // ════════════════════════════════════════════════════════════════
    //  DUMP PostgreSQL (Neon)
    // ════════════════════════════════════════════════════════════════

    /**
     * Dump PostgreSQL lengkap: sequence + struktur tabel + data + constraint + index,
     * dengan urutan restore yang aman terhadap foreign key.
     *
     * @param  array{tables:int,rows:int}  $stats
     * @param  callable|null  $progress  fn(int $percent, string $label)
     */
    protected function dumpPostgres($handle, array &$stats, ?callable $progress = null): void
    {
        $pdo = $this->pdo;

        $this->checkCancelled();
        if ($progress) {
            $progress(1, 'Membaca struktur database…');
        }

        fwrite($handle, "SET client_encoding = 'UTF8';\n");
        fwrite($handle, "SET standard_conforming_strings = on;\n\n");

        $tables = $pdo->query(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        )->fetchAll(PDO::FETCH_COLUMN);

        $sequences = $pdo->query(
            "SELECT c.relname FROM pg_class c
             JOIN pg_namespace n ON n.oid = c.relnamespace
             WHERE n.nspname = 'public' AND c.relkind = 'S'
             ORDER BY 1"
        )->fetchAll(PDO::FETCH_COLUMN);

        $stats['tables'] = count($tables);

        // 1) Drop table dulu (CASCADE menghapus sequence milik tabel).
        foreach ($tables as $table) {
            fwrite($handle, 'DROP TABLE IF EXISTS "'.$table."\" CASCADE;\n");
        }
        fwrite($handle, "\n");

        // 2) Drop sisa sequence yang tidak dimiliki tabel, lalu buat ulang semua.
        $ownedSeqs = $pdo->query(
            "SELECT seq.relname AS seq, tab.relname AS tbl, att.attname AS col
             FROM pg_depend dep
             JOIN pg_class seq ON seq.oid = dep.objid AND seq.relkind = 'S'
             JOIN pg_class tab ON tab.oid = dep.refobjid
             JOIN pg_attribute att ON att.attrelid = tab.oid AND att.attnum = dep.refobjsubid
             JOIN pg_namespace n ON n.oid = seq.relnamespace
             WHERE n.nspname = 'public' AND dep.deptype = 'a'"
        )->fetchAll(PDO::FETCH_ASSOC);

        $ownedBy = [];
        foreach ($ownedSeqs as $o) {
            $ownedBy[$o['seq']] = ['table' => $o['tbl'], 'column' => $o['col']];
        }

        foreach ($sequences as $seq) {
            fwrite($handle, 'DROP SEQUENCE IF EXISTS "'.$seq."\";\n");
        }
        foreach ($sequences as $seq) {
            fwrite($handle, 'CREATE SEQUENCE "'.$seq."\";\n");
        }
        fwrite($handle, "\n");

        // 3) Struktur tabel — AMBIL SEMUA metadata kolom dalam 1 query
        //    (hindari 1 query per tabel = N round-trip ke DB remote).
        $colRows = $pdo->query(
            "SELECT c.table_name,
                    a.attname,
                    format_type(a.atttypid, a.atttypmod) AS typ,
                    a.attnotnull,
                    pg_get_expr(d.adbin, d.adrelid) AS def
             FROM information_schema.columns c
             JOIN pg_attribute a
               ON a.attrelid = (c.table_name::regclass)
              AND a.attname = c.column_name
              AND a.attnum > 0 AND NOT a.attisdropped
             LEFT JOIN pg_attrdef d ON d.adrelid = a.attrelid AND d.adnum = a.attnum
             WHERE c.table_schema = 'public'
             ORDER BY c.table_name, c.ordinal_position"
        )->fetchAll(PDO::FETCH_ASSOC);

        $colsByTable = [];
        $byteaByTable = [];
        foreach ($colRows as $c) {
            $colsByTable[$c['table_name']][] = $c;
            if ($c['typ'] === 'bytea') {
                $byteaByTable[$c['table_name']][$c['attname']] = true;
            }
        }

        foreach ($tables as $table) {
            $cols = $colsByTable[$table] ?? [];
            $defs = [];
            foreach ($cols as $c) {
                $line = '    "'.$c['attname'].'" '.$c['typ'];
                if ($c['def'] !== null && $c['def'] !== '') {
                    $line .= ' DEFAULT '.$c['def'];
                }
                if ($c['attnotnull']) {
                    $line .= ' NOT NULL';
                }
                $defs[] = $line;
            }

            fwrite($handle, 'CREATE TABLE "'.$table."\" (\n".implode(",\n", $defs)."\n);\n\n");
        }

        // 3.5) Ikat sequence ke kolom serialnya (setelah tabel dibuat).
        if (! empty($ownedBy)) {
            foreach ($ownedBy as $seq => $owner) {
                fwrite($handle, 'ALTER SEQUENCE "'.$seq.'" OWNED BY "'.$owner['table'].'"."'.$owner['column']."\";\n");
            }
            fwrite($handle, "\n");
        }

        // 4) Data — hitung jumlah baris SELURUH tabel dalam 1 query,
        //    lalu lewati tabel kosong agar tak membuang round-trip ke DB remote.
        $countParts = [];
        foreach ($tables as $table) {
            $countParts[] = 'SELECT '.$pdo->quote($table).'::text AS t, count(*) AS n FROM "'.$table.'"';
        }
        $rowCounts = [];
        if ($countParts) {
            $rs = $pdo->query(implode(' UNION ALL ', $countParts));
            foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $rowCounts[$r['t']] = (int) $r['n'];
            }
        }

        $tableCount = count($tables);
        foreach ($tables as $i => $table) {
            $this->checkCancelled();

            $n = $rowCounts[$table] ?? 0;
            if ($n <= 0) {
                if ($progress) {
                    $progress((int) round(($i + 1) / max(1, $tableCount) * 100), 'Melewati tabel kosong '.($i + 1).'/'.$tableCount.': '.$table);
                }
                continue;
            }

            if ($progress) {
                $progress((int) round(($i + 1) / max(1, $tableCount) * 100), 'Dump data tabel '.($i + 1).'/'.$tableCount.': '.$table);
            }
            $stats['rows'] += $this->writePgInserts($handle, $table, $byteaByTable[$table] ?? []);
        }

        $this->checkCancelled();
        if ($progress) {
            $progress(100, 'Menulis constraint & index…');
        }

        // 5) Constraint PRIMARY KEY / UNIQUE / CHECK (setelah data, agar cepat & aman).
        $cons = $pdo->query(
            "SELECT c.conrelid::regclass::text AS tbl, c.conname, c.conindid::regclass::text AS idx, pg_get_constraintdef(c.oid) AS def
             FROM pg_constraint c
             WHERE c.connamespace = 'public'::regnamespace AND c.contype IN ('p','u','c')
             ORDER BY CASE c.contype WHEN 'p' THEN 0 WHEN 'u' THEN 1 ELSE 2 END, c.conname"
        )->fetchAll(PDO::FETCH_ASSOC);

        if ($cons) {
            fwrite($handle, "\n");
            foreach ($cons as $c) {
                $rawTbl = trim(str_replace('public.', '', $c['tbl']), '"');
                fwrite($handle, 'ALTER TABLE ONLY "'.$rawTbl.'" ADD CONSTRAINT "'.$c['conname'].'" '.$c['def'].";\n");
            }
        }

        // Nama index yang "dimiliki" constraint (primary/unique/exclusion) — tidak boleh dibuat ulang manual.
        $constraintIndexNames = array_column($cons ?? [], 'idx');
        $constraintNames = array_column($cons ?? [], 'conname');

        // 6) Index non-konstraint.
        $indexes = $pdo->query(
            "SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'public' ORDER BY indexname"
        )->fetchAll(PDO::FETCH_ASSOC);

        $extraIndexes = array_filter($indexes, function ($i) use ($constraintIndexNames, $constraintNames) {
            return ! in_array($i['indexname'], $constraintIndexNames, true)
                && ! in_array($i['indexname'], $constraintNames, true);
        });

        if ($extraIndexes) {
            fwrite($handle, "\n");
            foreach ($extraIndexes as $i) {
                fwrite($handle, $i['indexdef'].";\n");
            }
        }

        // 7) Nilai sequence terakhir (1 query UNION ALL, bukan per-sequence).
        if ($sequences) {
            fwrite($handle, "\n");
            $seqParts = [];
            foreach ($sequences as $seq) {
                $seqParts[] = 'SELECT '.$pdo->quote($seq).'::text AS s, last_value, is_called FROM "'.$seq.'"';
            }
            $seqVals = [];
            if ($seqParts) {
                $seqRows = $pdo->query(implode(' UNION ALL ', $seqParts))->fetchAll(PDO::FETCH_ASSOC);
                foreach ($seqRows as $r) {
                    $seqVals[$r['s']] = $r;
                }
            }
            foreach ($sequences as $seq) {
                $cur = $seqVals[$seq] ?? null;
                if ($cur) {
                    $called = ($cur['is_called'] === true || $cur['is_called'] === 't' || $cur['is_called'] === '1');
                    fwrite($handle, "SELECT pg_catalog.setval('".$seq."', ".(int) $cur['last_value'].', '.($called ? 'true' : 'false').");\n");
                }
            }
        }

        // 8) FOREIGN KEY paling akhir.
        $fks = $pdo->query(
            "SELECT c.conrelid::regclass::text AS tbl, c.conname, pg_get_constraintdef(c.oid) AS def
             FROM pg_constraint c
             WHERE c.connamespace = 'public'::regnamespace AND c.contype = 'f'
             ORDER BY c.conname"
        )->fetchAll(PDO::FETCH_ASSOC);

        if ($fks) {
            fwrite($handle, "\n");
            foreach ($fks as $fk) {
                $rawTbl = trim(str_replace('public.', '', $fk['tbl']), '"');
                fwrite($handle, 'ALTER TABLE ONLY "'.$rawTbl.'" ADD CONSTRAINT "'.$fk['conname'].'" '.$fk['def'].";\n");
            }
        }
    }

    /**
     * Tulis INSERT untuk satu tabel PostgreSQL dengan escaping PDO yang benar.
     * $byteaCols di-pass dari dumpPostgres (diambil sekaligus) agar tidak perlu
     * 1 query introspeksi per tabel.
     *
     * @param  array<string,bool>  $byteaCols
     */
    protected function writePgInserts($handle, string $table, array $byteaCols = []): int
    {
        $pdo = $this->pdo;

        $stmt = $pdo->query('SELECT * FROM "'.$table.'"');
        $colCount = $stmt->columnCount();
        if ($colCount === 0) {
            return 0;
        }

        $colNames = [];
        for ($c = 0; $c < $colCount; $c++) {
            $meta = $stmt->getColumnMeta($c);
            $colNames[] = $meta['name'];
        }

        $header = 'INSERT INTO "'.$table.'" ("'.implode('", "', $colNames)."\") VALUES\n";

        $rows = 0;
        $batch = 0;
        $buffer = '';

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $parts = [];
            foreach ($row as $i => $val) {
                $col = $colNames[$i];

                if ($val === null) {
                    $parts[] = 'NULL';
                } elseif (isset($byteaCols[$col])) {
                    $parts[] = strlen($val) > 0 ? "'\\x".bin2hex($val)."'::bytea" : "''::bytea";
                } elseif (is_bool($val)) {
                    $parts[] = $val ? 'true' : 'false';
                } elseif (is_int($val) || is_float($val)) {
                    $parts[] = (string) $val;
                } else {
                    $parts[] = $pdo->quote((string) $val);
                }
            }

            if ($batch > 0) {
                $buffer .= ",\n";
            }
            $buffer .= '('.implode(', ', $parts).')';
            $batch++;
            $rows++;

            if ($batch >= 200) {
                $this->checkCancelled();
                fwrite($handle, $header.$buffer.";\n");
                $buffer = '';
                $batch = 0;
            }
        }

        if ($batch > 0) {
            fwrite($handle, $header.$buffer.";\n");
        }

        return $rows;
    }

    // ════════════════════════════════════════════════════════════════
    //  RESTORE database
    // ════════════════════════════════════════════════════════════════

    protected function executeSql(string $sql, ?callable $progress = null): int
    {
        $driver = DB::connection()->getDriverName();

        ActivityLogger::disable();

        try {
            if ($driver === 'pgsql') {
                return $this->restorePostgres($sql, $progress);
            }

            if ($driver === 'sqlite') {
                return $this->restoreSqlite($sql);
            }

            return $this->restoreMysql($sql);
        } finally {
            ActivityLogger::enable();
        }
    }

    /**
     * Restore PostgreSQL via PDO — merge non-destruktif dalam satu transaksi.
     * Statement dump ditransformasi dulu: DROP dibuang, CREATE jadi IF NOT EXISTS,
     * ADD CONSTRAINT dibuat idempoten, INSERT jadi upsert — sehingga data yang
     * tidak ada di backup tetap bertahan.
     *
     * @param  callable|null  $progress  fn(int $percent, string $label)
     */
    protected function restorePostgres(string $sql, ?callable $progress = null): int
    {
        $pdo = $this->pdo;

        $this->checkCancelled();
        $this->pkCache = [];
        $this->loadExistingConstraints();

        // Putus koneksi lain agar merge tidak terhalang lock tabel.
        try {
            $pdo->exec(
                'SELECT pg_terminate_backend(pid) FROM pg_stat_activity
                 WHERE datname = current_database() AND pid <> pg_backend_pid()'
            );
        } catch (\Throwable $e) {
            // Tidak fatal.
        }

        // Matikan trigger FK selama restore (butuh role superuser; abaikan bila gagal).
        try {
            $pdo->exec('SET session_replication_role = replica');
        } catch (\Throwable $e) {
            // Abaikan — urutan dump sudah FK-safe (FK ditambahkan terakhir).
        }

        $statements = $this->splitStatements($sql);
        $total = count($statements);
        $executed = 0;
        $current = '';

        $pdo->beginTransaction();

        try {
            foreach ($statements as $i => $statement) {
                $this->checkCancelled();

                $current = $statement;
                $trimmed = trim($statement);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $transformed = $this->transformForMerge($trimmed);
                if ($transformed === null) {
                    continue;
                }

                $current = $transformed;
                $pdo->exec($transformed);
                $executed++;

                if ($progress) {
                    $progress((int) round(($i + 1) / max(1, $total) * 100), 'Menjalankan statement '.($i + 1).'/'.$total);
                }
            }

            // Samakan sequence dengan MAX(id) hasil merge agar INSERT berikutnya aman.
            $this->repairSequences();

            $pdo->commit();
        } catch (BackupCancelledException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new \RuntimeException('Pemulihan data gagal dijalankan.', 0, $e);
        } finally {
            try {
                $pdo->exec('SET session_replication_role = DEFAULT');
            } catch (\Throwable $e) {
                // Abaikan.
            }

            // Bersihkan cache skema Laravel agar query berikutnya melihat struktur baru.
            try {
                DB::connection()->getSchemaBuilder()->refreshTypes();
            } catch (\Throwable $e) {
                // Abaikan.
            }
        }

        return $executed;
    }

    /**
     * Transform satu statement dump agar aman untuk merge (non-destruktif).
     * Return null bila statement harus dilewati karena bersifat menghapus data.
     */
    protected function transformForMerge(string $statement): ?string
    {
        // Statement penghapus data — dibuang agar data di luar backup bertahan.
        if (preg_match('/^(DROP\s+(TABLE|SEQUENCE|INDEX|VIEW|MATERIALIZED)|TRUNCATE\b|DELETE\s+FROM)/i', $statement)) {
            return null;
        }

        // setval dari dump diganti repairSequences() setelah merge selesai.
        if (preg_match('/^SELECT\s+(pg_catalog\.)?setval\s*\(/i', $statement)) {
            return null;
        }

        // CREATE TABLE / SEQUENCE → IF NOT EXISTS (tabel yang sudah ada tidak disentuh).
        if (preg_match('/^CREATE\s+(TABLE|SEQUENCE)\s+(?!IF\s+NOT\s+EXISTS)/i', $statement)) {
            return preg_replace('/^CREATE\s+(TABLE|SEQUENCE)\s+/i', 'CREATE $1 IF NOT EXISTS ', $statement, 1);
        }

        // CREATE [UNIQUE] INDEX → IF NOT EXISTS.
        if (preg_match('/^CREATE\s+(UNIQUE\s+)?INDEX\s+(?!IF\s+NOT\s+EXISTS)/i', $statement)) {
            return preg_replace('/^CREATE\s+(UNIQUE\s+)?INDEX\s+/i', 'CREATE ${1}INDEX IF NOT EXISTS ', $statement, 1);
        }

        // ALTER TABLE ... ADD CONSTRAINT → lewati bila struktur setara sudah ada
        // (constraint bernama sama, atau tabel sudah punya PK), selain itu bungkus
        // dalam blok DO idempoten sebagai jaring pengaman.
        if (preg_match('/^ALTER\s+TABLE\s+.*ADD\s+CONSTRAINT\s/is', $statement) && ! str_contains($statement, '$$')) {
            if (preg_match('/^ALTER\s+TABLE\s+(?:ONLY\s+)?(?<tbl>"+[^"]+"+|"[^"]+"|[\w."]+)\s+ADD\s+CONSTRAINT\s+"(?<name>[^"]+)"\s+(?<def>.+)$/is', $statement, $cm)) {
                $rawTbl = trim($cm['tbl'], " \t\n\r\0\x0B\"");
                $rawTbl = str_replace('public.', '', $rawTbl);
                $rawTbl = trim($rawTbl, " \t\n\r\0\x0B\"");

                // Normalisasi statement agar selalu berformat: ALTER TABLE ONLY "nama_tabel" ADD CONSTRAINT "nama" ...
                $statement = 'ALTER TABLE ONLY "'.$rawTbl.'" ADD CONSTRAINT "'.$cm['name'].'" '.$cm['def'];

                // Constraint dengan nama sama sudah ada di tabel ini — tidak perlu dibuat ulang.
                if (isset($this->existingConstraints[$rawTbl.'.'.$cm['name']])) {
                    return null;
                }

                // Tabel sudah punya primary key — menambah PK kedua selalu gagal
                // dengan 42P16, apa pun nama constraint-nya.
                if (preg_match('/^PRIMARY\s+KEY\b/i', $cm['def']) && $this->tablePrimaryKeys($rawTbl) !== []) {
                    return null;
                }
            } else {
                // Fallback: bersihkan semua double quotes beruntun ("" -> ") pada klausa ALTER TABLE
                $statement = preg_replace('/""+/', '"', $statement);
            }

            return 'DO $$ BEGIN '.$statement.'; '
                .'EXCEPTION WHEN duplicate_object THEN NULL; '
                .'WHEN duplicate_table THEN NULL; '
                .'WHEN invalid_table_definition THEN NULL; '
                .'WHEN unique_violation THEN NULL; END $$';
        }

        // INSERT INTO "t" (kolom) VALUES → upsert berdasarkan primary key:
        // baris yang sudah ada diperbarui sesuai backup, baris baru ditambahkan.
        if (preg_match('/^INSERT\s+INTO\s+("(?<qt>[^"]+)"|(?<t>[A-Za-z_][A-Za-z0-9_]*))\s*\((?<cols>[^)]*)\)\s*VALUES\b/i', $statement, $m)) {
            $table = ($m['qt'] ?? '') !== '' ? $m['qt'] : ($m['t'] ?? '');

            preg_match_all('/"([^"]+)"|([A-Za-z_][A-Za-z0-9_]*)/', $m['cols'] ?? '', $cm);
            $cols = [];
            foreach ($cm[0] as $idx => $whole) {
                $cols[] = ($cm[1][$idx] ?? '') !== '' ? $cm[1][$idx] : $cm[2][$idx];
            }

            $pks = array_values(array_intersect($this->tablePrimaryKeys($table), $cols));

            // Tabel tanpa PK (atau kolom PK tak ada di daftar kolom) — biarkan
            // INSERT polos. Edge case: restore ulang bisa menduplikasi baris.
            if ($pks === []) {
                return $statement;
            }

            $nonPk = array_values(array_diff($cols, $pks));
            $clause = $nonPk === []
                ? 'DO NOTHING'
                : 'DO UPDATE SET '.implode(', ', array_map(fn ($c) => '"'.$c.'" = EXCLUDED."'.$c.'"', $nonPk));

            return $statement."\nON CONFLICT (".implode(', ', array_map(fn ($c) => '"'.$c.'"', $pks)).') '.$clause;
        }

        return $statement;
    }

    /**
     * Kolom primary key sebuah tabel (memoized). Array kosong bila tidak ada.
     *
     * @return array<int,string>
     */
    protected function tablePrimaryKeys(string $table): array
    {
        if (array_key_exists($table, $this->pkCache)) {
            return $this->pkCache[$table];
        }

        $cols = [];

        try {
            $stmt = $this->pdo->prepare(
                "SELECT a.attname
                 FROM pg_constraint c
                 JOIN pg_class t ON t.oid = c.conrelid
                 JOIN pg_namespace n ON n.oid = t.relnamespace
                 CROSS JOIN LATERAL unnest(c.conkey) WITH ORDINALITY AS u(attnum, ord)
                 JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = u.attnum
                 WHERE c.contype = 'p' AND n.nspname = 'public' AND t.relname = :table
                 ORDER BY u.ord"
            );
            $stmt->execute([':table' => $table]);
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            $cols = [];
        }

        return $this->pkCache[$table] = $cols;
    }

    /**
     * Muat semua nama constraint di skema public sekali (kunci "tabel.constraint"),
     * dipakai untuk melewatkan ADD CONSTRAINT yang sudah ada.
     */
    protected function loadExistingConstraints(): void
    {
        $this->existingConstraints = [];

        try {
            $rows = $this->pdo->query(
                "SELECT t.relname AS tbl, c.conname
                 FROM pg_constraint c
                 JOIN pg_class t ON t.oid = c.conrelid
                 WHERE c.connamespace = 'public'::regnamespace"
            )->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows ?: [] as $r) {
                $this->existingConstraints[$r['tbl'].'.'.$r['conname']] = true;
            }
        } catch (\Throwable $e) {
            // Biarkan kosong — blok DO di bawah tetap menjadi jaring pengaman.
        }
    }

    /**
     * Samakan sequence owned dengan MAX nilai kolomnya setelah merge,
     * agar INSERT berikutnya tidak bentrok dengan id hasil restore.
     */
    protected function repairSequences(): void
    {
        try {
            $owned = $this->pdo->query(
                "SELECT seq.relname AS seq, tab.relname AS tbl, att.attname AS col
                 FROM pg_depend dep
                 JOIN pg_class seq ON seq.oid = dep.objid AND seq.relkind = 'S'
                 JOIN pg_class tab ON tab.oid = dep.refobjid
                 JOIN pg_attribute att ON att.attrelid = tab.oid AND att.attnum = dep.refobjsubid
                 JOIN pg_namespace n ON n.oid = seq.relnamespace
                 WHERE n.nspname = 'public' AND dep.deptype = 'a'"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return;
        }

        foreach ($owned as $o) {
            $this->checkCancelled();

            try {
                $row = $this->pdo->query('SELECT MAX("'.$o['col'].'") FROM "'.$o['tbl'].'"')->fetch(PDO::FETCH_NUM);
                if ($row !== false && $row[0] !== null) {
                    $this->pdo->exec('SELECT pg_catalog.setval(\'"'.$o['seq'].'"\', '.(int) $row[0].', true)');
                }
            } catch (\Throwable $e) {
                // Best effort — lanjut ke sequence berikutnya.
            }
        }
    }

    // ════════════════════════════════════════════════════════════════
    //  Driver lama (MySQL / SQLite) — kompatibilitas mundur
    // ════════════════════════════════════════════════════════════════

    protected function dumpMysql($handle): void
    {
        $this->checkCancelled();

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $this->checkCancelled();
            $create = $this->pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_ASSOC);
            $ddl = $create['Create Table'] ?? ($create['Create View'] ?? null);
            if (! $ddl) {
                continue;
            }

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $ddl.";\n\n");

            $this->writeInsertsFast($handle, $table);
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    protected function dumpSqlite($handle): void
    {
        $this->checkCancelled();

        fwrite($handle, "PRAGMA foreign_keys=OFF;\n\n");

        $tables = $this->pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tables as $t) {
            $this->checkCancelled();
            $table = $t['name'];
            fwrite($handle, "DROP TABLE IF EXISTS \"{$table}\";\n");
            fwrite($handle, $t['sql'].";\n\n");
            $this->writeInsertsFast($handle, $table, '"');
            fwrite($handle, "\n");
        }

        fwrite($handle, "PRAGMA foreign_keys=ON;\n");
    }

    protected function writeInsertsFast($handle, string $table, string $quote = '`'): void
    {
        $q = $quote;
        $stmt = $this->pdo->query('SELECT * FROM '.$q.$table.$q);

        $colCount = $stmt->columnCount();

        if ($colCount === 0) {
            return;
        }

        $colNames = [];
        for ($c = 0; $c < $colCount; $c++) {
            $meta = $stmt->getColumnMeta($c);
            $colNames[] = $q.$meta['name'].$q;
        }
        $header = 'INSERT INTO '.$q.$table.$q.' ('.implode(',', $colNames).") VALUES\n";

        $batchSize = 0;
        $buffer = '';

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $parts = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $parts[] = 'NULL';
                } elseif (is_bool($val)) {
                    $parts[] = $val ? 'true' : 'false';
                } elseif (is_int($val) || is_float($val)) {
                    $parts[] = (string) $val;
                } else {
                    $s = (string) $val;
                    if ($s === '') {
                        $parts[] = "''";
                    } else {
                        $parts[] = "'".addslashes($s)."'";
                    }
                }
            }

            if ($batchSize > 0) {
                $buffer .= ",\n";
            }
            $buffer .= '('.implode(',', $parts).')';
            $batchSize++;

            if ($batchSize >= 500) {
                $this->checkCancelled();
                fwrite($handle, $header.$buffer.";\n");
                $buffer = '';
                $batchSize = 0;
            }
        }

        if ($batchSize > 0) {
            fwrite($handle, $header.$buffer.";\n");
        }
    }

    protected function restoreMysql(string $sql): int
    {
        $this->checkCancelled();

        $config = config('database.connections.mysql');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? '';

        $mysqli = @new \mysqli($host, $username, $password, $database, (int) $port);

        if ($mysqli->connect_error) {
            throw new \RuntimeException('Gagal koneksi MySQL: '.$mysqli->connect_error);
        }

        $mysqli->set_charset('utf8mb4');
        $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
        $mysqli->query('SET NAMES utf8mb4');

        $executed = 0;

        if ($mysqli->multi_query($sql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
                $executed++;
            } while ($mysqli->more_results() && $mysqli->next_result());
        }

        if ($mysqli->error) {
            throw new \RuntimeException('Error restore: '.$mysqli->error);
        }

        $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
        $mysqli->close();

        return $executed;
    }

    protected function restoreSqlite(string $sql): int
    {
        $this->checkCancelled();

        $statements = $this->splitStatements($sql);
        $executed = 0;

        $this->pdo->exec('PRAGMA foreign_keys=OFF');

        foreach ($statements as $statement) {
            $this->checkCancelled();

            $trimmed = trim($statement);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            try {
                $this->pdo->exec($trimmed);
                $executed++;
            } catch (\Throwable $e) {
                // skip
            }
        }

        $this->pdo->exec('PRAGMA foreign_keys=ON');

        return $executed;
    }

    /**
     * Pisah file SQL jadi statement — hormati string '...', "...", `...`,
     * dollar-quote ($$...$$), dan komentar.
     *
     * @return array<int,string>
     */
    protected function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $i = 0;

        while ($i < $len) {
            $char = $sql[$i];

            // Dollar-quoted string ($tag$ ... $tag$) — dipakai function/view PL/pgSQL.
            if ($char === '$' && preg_match('/\G(\$[A-Za-z0-9_]*\$)/', $sql, $m, 0, $i)) {
                $tag = $m[1];
                $current .= $tag;
                $i += strlen($tag);
                $end = strpos($sql, $tag, $i);
                if ($end === false) {
                    $current .= substr($sql, $i);
                    $i = $len;
                } else {
                    $current .= substr($sql, $i, $end - $i + strlen($tag));
                    $i = $end + strlen($tag);
                }

                continue;
            }

            // /* ... */ block comment
            if ($char === '/' && $i + 1 < $len && $sql[$i + 1] === '*') {
                $i += 2;
                while ($i < $len) {
                    if ($sql[$i] === '*' && $i + 1 < $len && $sql[$i + 1] === '/') {
                        $i += 2;
                        break;
                    }
                    $i++;
                }

                continue;
            }

            // -- single-line comment
            if ($char === '-' && $i + 1 < $len && $sql[$i + 1] === '-') {
                $i += 2;
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }

                continue;
            }

            // Single-quoted string — handle \' dan '' escapes.
            if ($char === "'") {
                $current .= $char;
                $i++;
                while ($i < $len) {
                    $c = $sql[$i];
                    if ($c === '\\') {
                        $current .= $c;
                        $i++;
                        if ($i < $len) {
                            $current .= $sql[$i];
                            $i++;
                        }
                    } elseif ($c === "'" && $i + 1 < $len && $sql[$i + 1] === "'") {
                        $current .= "''";
                        $i += 2;
                    } elseif ($c === "'") {
                        $current .= $c;
                        $i++;
                        break;
                    } else {
                        $current .= $c;
                        $i++;
                    }
                }

                continue;
            }

            // Double-quoted identifier ("nama_tabel") — handle "" escape.
            if ($char === '"') {
                $current .= $char;
                $i++;
                while ($i < $len) {
                    $c = $sql[$i];
                    if ($c === '"' && $i + 1 < $len && $sql[$i + 1] === '"') {
                        $current .= '""';
                        $i += 2;
                    } elseif ($c === '"') {
                        $current .= $c;
                        $i++;
                        break;
                    } else {
                        $current .= $c;
                        $i++;
                    }
                }

                continue;
            }

            // Backtick-quoted identifier (MySQL).
            if ($char === '`') {
                $current .= $char;
                $i++;
                while ($i < $len && $sql[$i] !== '`') {
                    $current .= $sql[$i];
                    $i++;
                }
                if ($i < $len) {
                    $current .= $sql[$i];
                    $i++;
                }

                continue;
            }

            // Statement delimiter.
            if ($char === ';') {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $current;
                }
                $current = '';
                $i++;

                continue;
            }

            $current .= $char;
            $i++;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Daftar file backup (nama, ukuran, tanggal) terbaru dulu dari disk backup.
     */
    public static function listBackups(): array
    {
        $disk = Storage::disk(self::diskName());
        $tz = config('app.timezone', 'Asia/Makassar');

        try {
            $files = $disk->files(self::DIR);
        } catch (\Throwable $e) {
            return [];
        }

        return collect($files)
            ->filter(fn ($f) => str_ends_with($f, '.zip') || str_ends_with($f, '.sql'))
            ->map(function ($f) use ($disk, $tz) {
                $modified = $disk->lastModified($f);

                return [
                    'name' => basename($f),
                    'path' => $f,
                    'size' => $disk->size($f),
                    'modified' => $modified,
                    'modified_at' => Carbon::createFromTimestamp($modified, $tz)->format('Y-m-d H:i:s'),
                ];
            })
            ->sortByDesc('modified')
            ->values()
            ->all();
    }

    /**
     * Unduh file backup dari disk backup ke file lokal sementara.
     * Return path lokal sementara, atau null jika gagal/tidak ada.
     */
    public static function downloadToTemp(string $relativePath): ?string
    {
        $disk = Storage::disk(self::diskName());

        if (! $disk->exists($relativePath)) {
            return null;
        }

        $suffix = pathinfo($relativePath, PATHINFO_EXTENSION);
        $tmp = tempnam(sys_get_temp_dir(), 'dlhrestore_');
        if ($suffix !== '') {
            $newTmp = $tmp.'.'.$suffix;
            rename($tmp, $newTmp);
            $tmp = $newTmp;
        }

        $stream = $disk->readStream($relativePath);
        if (! $stream) {
            @unlink($tmp);

            return null;
        }

        $out = fopen($tmp, 'w');
        if (! $out) {
            @unlink($tmp);

            return null;
        }

        stream_copy_to_stream($stream, $out);
        fclose($out);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $tmp;
    }

    /**
     * Validasi nama file backup (whitelist, cegah path traversal).
     * Return path relatif pada disk backup, atau null jika tidak valid/tidak ada.
     */
    public static function safePath(string $filename): ?string
    {
        $filename = basename($filename);
        if (! preg_match('/^[A-Za-z0-9._-]+\.(zip|sql)$/', $filename)) {
            return null;
        }

        $relative = self::DIR.'/'.$filename;

        try {
            if (! Storage::disk(self::diskName())->exists($relative)) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $relative;
    }
}
