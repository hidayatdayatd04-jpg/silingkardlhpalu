<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;

/**
 * Backup & restore database MySQL via PDO murni (tanpa binary mysqldump).
 *
 * dump()    → tulis file .sql lengkap (DDL + INSERT) ke disk privat.
 * restore() → jalankan statement dari file .sql (destruktif — menimpa data).
 */
class DatabaseBackup
{
    public const DISK = 'local';
    public const DIR = 'backups';

    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
    }

    /**
     * Buat dump database → path relatif di disk 'local'.
     */
    public function dump(?string $filename = null): string
    {
        $filename ??= 'backup-'.now()->format('Ymd-His').'.sql';
        $relativePath = self::DIR.'/'.$filename;

        Storage::disk(self::DISK)->makeDirectory(self::DIR);
        $fullPath = Storage::disk(self::DISK)->path($relativePath);

        $handle = fopen($fullPath, 'w');

        $driver = DB::connection()->getDriverName();

        fwrite($handle, "-- DLH Palu Database Backup\n");
        fwrite($handle, '-- Generated: '.now()->toDateTimeString()."\n");
        fwrite($handle, '-- Driver: '.$driver."\n\n");

        if ($driver === 'sqlite') {
            $this->dumpSqlite($handle);
        } else {
            $this->dumpMysql($handle);
        }

        fclose($handle);

        return $relativePath;
    }

    protected function dumpMysql($handle): void
    {
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // DDL
            $create = $this->pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_ASSOC);
            $ddl = $create['Create Table'] ?? ($create['Create View'] ?? null);
            if (! $ddl) {
                continue;
            }

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $ddl.";\n\n");

            // Data — chunked untuk hemat memori
            $this->writeInserts($handle, $table);
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
            $this->writeInserts($handle, $table, true);
            fwrite($handle, "\n");
        }

        fwrite($handle, "PRAGMA foreign_keys=ON;\n");
    }

    /**
     * Tulis statement INSERT untuk sebuah tabel secara batch.
     */
    protected function writeInserts($handle, string $table, bool $sqlite = false): void
    {
        $quote = $sqlite ? '"' : '`';
        $stmt = $this->pdo->query('SELECT * FROM '.$quote.$table.$quote);

        $batch = [];
        $columns = null;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($columns === null) {
                $columns = array_keys($row);
            }

            $values = array_map(fn ($v) => $this->quoteValue($v), array_values($row));
            $batch[] = '('.implode(',', $values).')';

            if (count($batch) >= 200) {
                $this->flushBatch($handle, $table, $columns, $batch, $quote);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->flushBatch($handle, $table, $columns, $batch, $quote);
        }
    }

    protected function flushBatch($handle, string $table, array $columns, array $batch, string $quote): void
    {
        $cols = implode(',', array_map(fn ($c) => $quote.$c.$quote, $columns));
        fwrite($handle, 'INSERT INTO '.$quote.$table.$quote.' ('.$cols.") VALUES\n");
        fwrite($handle, implode(",\n", $batch).";\n");
    }

    protected function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $this->pdo->quote((string) $value);
    }

    /**
     * Restore dari file .sql (destruktif). Return jumlah statement dieksekusi.
     */
    public function restore(string $fullPath): int
    {
        if (! is_file($fullPath)) {
            throw new \RuntimeException('File backup tidak ditemukan.');
        }

        $sql = file_get_contents($fullPath);
        $statements = $this->splitStatements($sql);

        $driver = DB::connection()->getDriverName();
        $executed = 0;

        // Nonaktifkan audit log agar restore tidak membanjiri log.
        ActivityLogger::disable();

        try {
            if ($driver !== 'sqlite') {
                $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            }

            foreach ($statements as $statement) {
                $trimmed = trim($statement);
                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }
                $this->pdo->exec($trimmed);
                $executed++;
            }

            if ($driver !== 'sqlite') {
                $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }
        } finally {
            ActivityLogger::enable();
        }

        return $executed;
    }

    /**
     * Pisah file SQL jadi statement, hormati string literal ber-quote.
     *
     * @return array<int,string>
     */
    protected function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && $prev !== '\\') {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
                $current .= $char;

                continue;
            }

            if ($char === ';') {
                $statements[] = $current;
                $current = '';

                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Daftar file backup (nama, ukuran, tanggal) terbaru dulu.
     */
    public static function listBackups(): array
    {
        $disk = Storage::disk(self::DISK);
        if (! $disk->exists(self::DIR)) {
            return [];
        }

        return collect($disk->files(self::DIR))
            ->filter(fn ($f) => str_ends_with($f, '.sql'))
            ->map(fn ($f) => [
                'name'     => basename($f),
                'path'     => $f,
                'size'     => $disk->size($f),
                'modified' => $disk->lastModified($f),
            ])
            ->sortByDesc('modified')
            ->values()
            ->all();
    }

    /**
     * Validasi nama file (whitelist, cegah path traversal).
     */
    public static function safePath(string $filename): ?string
    {
        $filename = basename($filename);
        if (! preg_match('/^[A-Za-z0-9._-]+\.sql$/', $filename)) {
            return null;
        }

        $relative = self::DIR.'/'.$filename;
        if (! Storage::disk(self::DISK)->exists($relative)) {
            return null;
        }

        return Storage::disk(self::DISK)->path($relative);
    }
}
