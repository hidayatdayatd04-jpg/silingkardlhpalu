<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use ZipArchive;

/**
 * Backup & restore database + file uploads via PDO murni.
 *
 * dump()    → tulis file .zip (database.sql + storage/app/public/*) ke disk privat.
 * restore() → jalankan statement dari .sql + ekstrak file upload (destruktif — menimpa data).
 */
class DatabaseBackup
{
    public const DISK = 'local';
    public const DIR = 'backups';
    public const FILE_DISK = 'public';
    public const FILE_DISK_DIR = '';

    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Timeout 60 detik supaya tidak hang selamanya.
        try {
            $this->pdo->exec('SET SESSION wait_timeout=60');
            $this->pdo->exec('SET SESSION net_read_timeout=60');
            $this->pdo->exec('SET SESSION net_write_timeout=60');
        } catch (\Throwable $e) {
            // Silently ignore — beberapa driver mungkin tidak support SET SESSION.
        }
    }

    /**
     * Buat backup lengkap (database + file upload) → path relatif di disk 'local'.
     *
     * Format: ZIP berisi database.sql + folder-file dari storage/app/public/.
     */
    public function dump(?string $filename = null): string
    {
        $filename ??= 'backup-'.now()->format('Ymd-His').'.zip';
        $relativePath = self::DIR.'/'.$filename;

        Storage::disk(self::DISK)->makeDirectory(self::DIR);
        $fullPath = Storage::disk(self::DISK)->path($relativePath);

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file ZIP backup.');
        }

        // 1) Dump database ke SQL string
        $sqlContent = $this->generateSqlDump();
        $zip->addFromString('database.sql', $sqlContent);

        // 2) Tambahkan semua file dari storage/app/public/ (disk 'public')
        $publicDisk = Storage::disk(self::FILE_DISK);
        $storagePath = $publicDisk->path('/');
        if (is_dir($storagePath)) {
            $this->addDirectoryToZip($zip, $storagePath, 'storage/public');
        }

        $zip->close();

        return $relativePath;
    }

    /**
     * Buat dump SQL ke string (tanpa file).
     */
    protected function generateSqlDump(): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'dbbackup_');

        try {
            $handle = fopen($tmpPath, 'w');

            $driver = DB::connection()->getDriverName();

            fwrite($handle, "-- DLH Palu Database Backup\n");
            fwrite($handle, '-- Generated: '.now()->toDateTimeString()."\n");
            fwrite($handle, '-- Driver: '.$driver."\n\n");

            if ($driver === 'sqlite') {
                $this->dumpSqlite($handle);
            } elseif ($driver === 'pgsql') {
                $this->dumpPostgres($handle);
            } else {
                $this->dumpMysql($handle);
            }

            fclose($handle);

            return file_get_contents($tmpPath);
        } finally {
            @unlink($tmpPath);
        }
    }

    /**
     * Rekursif tambahkan direktori ke ZIP.
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPrefix): void
    {
        // Normalisasi: hapus trailing separator sebelum menghitung panjang
        $baseDir = rtrim(str_replace('\\', '/', $directory), '/').'/';
        $baseLen = strlen($baseDir);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // Normalisasi path file ke forward slash
            $fullPath = str_replace('\\', '/', $file->getPathname());

            // Pastikan path dimulai dengan baseDir
            if (strpos($fullPath, $baseDir) !== 0) {
                continue;
            }

            $relativePath = substr($fullPath, $baseLen);
            if ($relativePath === '' || $relativePath === false) {
                continue;
            }

            $zipPath = $zipPrefix.'/'.$relativePath;

            if ($file->isDir()) {
                $zip->addEmptyDir($zipPath);
            } else {
                $zip->addFile($file->getPathname(), $zipPath);
            }
        }
    }

    protected function dumpMysql($handle): void
    {
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
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
        fwrite($handle, "PRAGMA foreign_keys=OFF;\n\n");

        $tables = $this->pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tables as $t) {
            $table = $t['name'];
            fwrite($handle, "DROP TABLE IF EXISTS \"{$table}\";\n");
            fwrite($handle, $t['sql'].";\n\n");
            $this->writeInsertsFast($handle, $table, '"');
            fwrite($handle, "\n");
        }

        fwrite($handle, "PRAGMA foreign_keys=ON;\n");
    }

    /**
     * PostgreSQL dump (struktur + data). Struktur diambil dari information_schema
     * karena PostgreSQL tidak mendukung SHOW CREATE TABLE seperti MySQL.
     */
    protected function dumpPostgres($handle): void
    {
        $pdo = $this->pdo;

        $tables = $pdo->query(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $cols = $pdo->query(
                "SELECT column_name, data_type, is_nullable, column_default
                 FROM information_schema.columns
                 WHERE table_schema = 'public' AND table_name = ".$pdo->quote($table)."
                 ORDER BY ordinal_position"
            )->fetchAll(PDO::FETCH_ASSOC);

            $pk = $pdo->query(
                "SELECT a.attname FROM pg_index i
                 JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
                 WHERE i.indrelid = (SELECT oid FROM pg_class WHERE relname = ".$pdo->quote($table).")
                 AND i.indisprimary"
            )->fetchAll(PDO::FETCH_COLUMN);

            fwrite($handle, 'DROP TABLE IF EXISTS "'.$table."\" CASCADE;\n");

            $defs = [];
            foreach ($cols as $c) {
                $line = '"'.$c['column_name'].'" '.$this->mapPgType($c['data_type']);
                if (strtoupper((string) $c['is_nullable']) === 'NO') {
                    $line .= ' NOT NULL';
                }
                $defs[] = $line;
            }

            if (! empty($pk)) {
                $pkCols = implode(', ', array_map(fn ($k) => '"'.$k.'"', $pk));
                $defs[] = 'PRIMARY KEY ('.$pkCols.')';
            }

            fwrite($handle, 'CREATE TABLE "'.$table."\" (\n  ".implode(",\n  ", $defs)."\n);\n\n");

            $this->writeInsertsFast($handle, $table, '"');
            fwrite($handle, "\n");
        }
    }

    /**
     * Petakan tipe data PostgreSQL ke tipe SQL generik agar dump dapat di-restore.
     */
    protected function mapPgType(string $type): string
    {
        return match ($type) {
            'integer', 'int2', 'int4' => 'integer',
            'bigint', 'int8' => 'bigint',
            'smallint' => 'smallint',
            'numeric', 'decimal' => 'numeric',
            'real', 'float4' => 'real',
            'double precision', 'float8' => 'double precision',
            'boolean' => 'boolean',
            'bytea' => 'bytea',
            'uuid' => 'uuid',
            'date' => 'date',
            'time', 'time without time zone', 'time with time zone' => 'time',
            'timestamp', 'timestamp without time zone', 'timestamp with time zone' => 'timestamp',
            'json', 'jsonb' => 'jsonb',
            'text' => 'text',
            default => 'text',
        };
    }

    /**
     * INSERT dump — versi cepat tanpa function call overhead per baris.
     */
    protected function writeInsertsFast($handle, string $table, string $quote = '`'): void
    {
        $q = $quote;
        $stmt = $this->pdo->query('SELECT * FROM '.$q.$table.$q);

        $colCount = $stmt->columnCount();

        if ($colCount === 0) {
            return;
        }

        // Bangun header INSERT sekali
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
                fwrite($handle, $header.$buffer.";\n");
                $buffer = '';
                $batchSize = 0;
            }
        }

        if ($batchSize > 0) {
            fwrite($handle, $header.$buffer.";\n");
        }
    }

    /**
     * Restore dari file backup (.zip atau .sql lama). Return jumlah statement dieksekusi.
     */
    public function restore(string $fullPath): int
    {
        if (! is_file($fullPath)) {
            throw new \RuntimeException('File backup tidak ditemukan.');
        }

        // Deteksi apakah ZIP atau SQL biasa
        $isZip = preg_match('/\.zip$/i', $fullPath);

        if ($isZip) {
            return $this->restoreZip($fullPath);
        }

        return $this->restoreSqlFile($fullPath);
    }

    /**
     * Restore dari file .zip (database + file upload).
     */
    protected function restoreZip(string $zipPath): int
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Gagal membuka file ZIP backup.');
        }

        // 1) Ekstrak file upload terlebih dulu
        $publicDisk = Storage::disk(self::FILE_DISK);
        $storagePath = $publicDisk->path('/');

        // Hapus folder storage/app/public/ yang lama (kecuali .gitignore)
        if (is_dir($storagePath)) {
            $this->removeDirectoryContents($storagePath);
        }

        // Ekstrak semua file dari ZIP ke storage/app/public/
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === null || $name === 'database.sql') {
                continue;
            }

            // Normalisasi path
            $name = str_replace('\\', '/', $name);

            // Handle paths starting with 'storage/public/'
            $targetPath = $storagePath;
            if (str_starts_with($name, 'storage/public/')) {
                $relativeToFile = substr($name, strlen('storage/public/'));
                if ($relativeToFile !== '') {
                    $targetPath = $storagePath.$relativeToFile;
                }
            } else {
                // Skip file yang bukan file upload
                continue;
            }

            // Buat direktori jika perlu
            $dir = dirname($targetPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Jika bukan direktori, ekstrak file
            if (! str_ends_with($name, '/')) {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($targetPath, $content);
                }
            }
        }

        // 2) Restore database dari database.sql
        $sqlContent = $zip->getFromName('database.sql');
        $zip->close();

        if ($sqlContent === false || trim($sqlContent) === '') {
            throw new \RuntimeException('File database.sql tidak ditemukan atau kosong dalam backup.');
        }

        return $this->executeSql($sqlContent);
    }

    /**
     * Restore dari file .sql biasa (backward compatibility).
     */
    protected function restoreSqlFile(string $fullPath): int
    {
        $sql = file_get_contents($fullPath);

        if (trim($sql) === '') {
            throw new \RuntimeException('File backup kosong.');
        }

        return $this->executeSql($sql);
    }

    /**
     * Eksekusi SQL dump.
     */
    protected function executeSql(string $sql): int
    {
        $driver = DB::connection()->getDriverName();

        ActivityLogger::disable();

        try {
            if ($driver === 'sqlite') {
                return $this->restoreSqlite($sql);
            }

            return $this->restoreMysql($sql);
        } finally {
            ActivityLogger::enable();
        }
    }

    /**
     * Hapus isi direktori (tanpa menghapus direktori itu sendiri).
     */
    protected function removeDirectoryContents(string $directory): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                // Jangan hapus .gitignore
                if ($item->getFilename() !== '.gitignore') {
                    @unlink($item->getPathname());
                }
            }
        }
    }

    /**
     * Restore MySQL via mysqli_multi_query — eksekusi seluruh SQL sekaligus.
     * Jauh lebih cepat daripada exec() per-statement.
     */
    protected function restoreMysql(string $sql): int
    {
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

    /**
     * Restore SQLite via PDO.
     */
    protected function restoreSqlite(string $sql): int
    {
        $statements = $this->splitStatements($sql);
        $executed = 0;

        $this->pdo->exec('PRAGMA foreign_keys=OFF');

        foreach ($statements as $statement) {
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
     * Pisah file SQL jadi statement — hormati string, backtick, komentar.
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

            // Single-quoted string — handle BOTH \' and '' escapes
            if ($char === "'") {
                $current .= $char;
                $i++;
                while ($i < $len) {
                    $c = $sql[$i];
                    if ($c === '\\') {
                        // Backslash escape — skip \ + next char (termasuk \')
                        $current .= $c;
                        $i++;
                        if ($i < $len) {
                            $current .= $sql[$i];
                            $i++;
                        }
                    } elseif ($c === "'" && $i + 1 < $len && $sql[$i + 1] === "'") {
                        // '' escape — double single-quote
                        $current .= "''";
                        $i += 2;
                    } elseif ($c === "'") {
                        // Closing quote
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

            // Backtick-quoted identifier
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

            // Statement delimiter
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
     * Daftar file backup (nama, ukuran, tanggal) terbaru dulu.
     * Mendukung .zip (baru) dan .sql (lama).
     */
    public static function listBackups(): array
    {
        $disk = Storage::disk(self::DISK);
        if (! $disk->exists(self::DIR)) {
            return [];
        }

        $tz = config('app.timezone', 'Asia/Makassar');

        return collect($disk->files(self::DIR))
            ->filter(fn ($f) => str_ends_with($f, '.zip') || str_ends_with($f, '.sql'))
            ->map(fn ($f) => [
                'name'     => basename($f),
                'path'     => $f,
                'size'     => $disk->size($f),
                'modified' => $disk->lastModified($f),
                'modified_at' => \Carbon\Carbon::createFromTimestamp($disk->lastModified($f), $tz)->format('Y-m-d H:i:s'),
            ])
            ->sortByDesc('modified')
            ->values()
            ->all();
    }

    /**
     * Validasi nama file (whitelist, cegah path traversal).
     * Mendukung .zip (baru) dan .sql (lama).
     */
    public static function safePath(string $filename): ?string
    {
        $filename = basename($filename);
        if (! preg_match('/^[A-Za-z0-9._-]+\.(zip|sql)$/', $filename)) {
            return null;
        }

        $relative = self::DIR.'/'.$filename;
        if (! Storage::disk(self::DISK)->exists($relative)) {
            return null;
        }

        return Storage::disk(self::DISK)->path($relative);
    }
}
