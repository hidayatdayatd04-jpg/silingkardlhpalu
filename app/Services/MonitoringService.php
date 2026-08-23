<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Ringkasan pemakaian infrastruktur: storage Backblaze B2 dan database Neon PostgreSQL.
 *
 * Hasil di-cache 5 menit agar halaman settings tidak memicu listing bucket /
 * query database setiap kali dibuka. Kegagalan di-handle gracefully.
 */
class MonitoringService
{
    /** Waktu cache (detik). */
    protected const TTL_SECONDS = 300;

    /**
     * Total storage terpakai di seluruh bucket Backblaze B2.
     *
     * @return array{bytes:int,human:string,files:int,bucket:string,limit_bytes:float,limit_human:string,percent:float,error:mixed,message?:string,details?:string}
     */
    public function b2Storage(): array
    {
        return Cache::remember('monitoring.b2_storage', self::TTL_SECONDS, function () {
            try {
                $adapter = Storage::disk('b2')->getAdapter();

                $bytes = 0;
                $files = 0;

                foreach ($adapter->listContents('', true) as $item) {
                    if (method_exists($item, 'isFile') && $item->isFile()) {
                        $bytes += (int) $item->fileSize();
                        $files++;
                    }
                }

                $limit = (float) config('monitoring.b2_limit_bytes', 10 * 1024 * 1024 * 1024);

                return [
                    'error' => null,
                    'bytes' => $bytes,
                    'human' => \Illuminate\Support\Number::fileSize($bytes, 2),
                    'files' => $files,
                    'bucket' => (string) config('filesystems.disks.b2.bucket', '-'),
                    'limit_bytes' => $limit,
                    'limit_human' => \Illuminate\Support\Number::fileSize($limit, 0),
                    'plan' => (string) config('monitoring.b2_plan', 'Free Tier'),
                    'percent' => $limit > 0 ? round($bytes / $limit * 100, 1) : 0,
                ];
            } catch (\Throwable $e) {
                return $this->errorState('Backblaze B2', $e, 10 * 1024 * 1024 * 1024, (string) config('monitoring.b2_plan', 'Free Tier'));
            }
        });
    }

    /**
     * Ukuran database yang terpakai di Neon PostgreSQL.
     *
     * @return array{bytes:int,human:string,database:string,host:string|null,limit_bytes:float,limit_human:string,percent:float,error:mixed,message?:string,details?:string}
     */
    public function neonDatabase(): array
    {
        return Cache::remember('monitoring.neon_database', self::TTL_SECONDS, function () {
            try {
                $connection = DB::connection();
                $size = (int) $connection->selectOne('SELECT pg_database_size(current_database()) AS size')->size;
                $limit = (float) config('monitoring.neon_limit_bytes', 512 * 1024 * 1024);

                return [
                    'error' => null,
                    'bytes' => $size,
                    'human' => \Illuminate\Support\Number::fileSize($size, 2),
                    'database' => $connection->getDatabaseName(),
                    'host' => config('database.connections.pgsql.host'),
                    'limit_bytes' => $limit,
                    'limit_human' => \Illuminate\Support\Number::fileSize($limit, 0),
                    'plan' => (string) config('monitoring.neon_plan', 'Free Tier'),
                    'percent' => $limit > 0 ? round($size / $limit * 100, 1) : 0,
                ];
            } catch (\Throwable $e) {
                return $this->errorState('Neon PostgreSQL', $e, 512 * 1024 * 1024, (string) config('monitoring.neon_plan', 'Free Tier'));
            }
        });
    }

    /**
     * State saat terjadi kegagalan (koneksi/query error) — card tetap tampil
     * dengan pesan yang jelas tanpa merusak halaman.
     */
    protected function errorState(string $label, \Throwable $e, float $limit, string $plan = ''): array
    {
        // Detail teknis cukup dicatat di log server, bukan di UI admin.
        report($e);

        return [
            'error' => true,
            // Pesan netral untuk admin — detail teknis hanya masuk log server.
            'message' => 'Data kapasitas gagal dimuat. Silakan muat ulang halaman.',
            'details' => $e->getMessage(),
            'bytes' => 0,
            'human' => '—',
            'files' => 0,
            'bucket' => (string) config('filesystems.disks.b2.bucket', '-'),
            'database' => config('database.connections.pgsql.database', '-'),
            'host' => config('database.connections.pgsql.host'),
            'limit_bytes' => $limit,
            'limit_human' => \Illuminate\Support\Number::fileSize($limit, 0),
            'plan' => $plan,
            'percent' => 0,
        ];
    }
}
