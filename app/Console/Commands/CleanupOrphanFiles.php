<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\ObjekPengawasanDokumen;
use App\Models\PelanggaranMedia;
use App\Models\PengaduanPengendalianFoto;
use App\Models\PengaduanRthFoto;
use App\Models\PengaduanSampahFoto;
use App\Models\PengaduanTataPenataanFoto;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\Sanksi;
use App\Models\SidakMedia;
use App\Models\SosialisasiFile;
use App\Models\SosialisasiPeserta;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Deteksi & pembersihan file yatim (orphan) di storage B2.
 *
 * Membandingkan SELURUH objek di disk (default: 'public' = B2) dengan
 * referensi file yang tersimpan di database (semua kolom path dari seluruh
 * modul upload Admin & Public). Objek yang tidak lagi memiliki referensi —
 * dan sudah melewati grace period — dianggap yatim dan dihapus lewat
 * FileUploadService agar semua versi lama objek di B2 ikut ter-purge.
 *
 * Pengaman:
 * - Default dry-run: tidak ada yang dihapus tanpa flag --delete.
 * - Bila database tidak terbaca / semua sumber referensi gagal dibaca,
 *   perintah menolak menghapus apa pun (exit error).
 * - Grace period (--days, default 2): file yang baru diunggah tidak akan
 *   disentuh, melindungi file yang masih dalam antrean proses.
 * - Folder sistem (livewire-tmp, backups, temp) dilewati karena memiliki
 *   mekanisme pembersihan masing-masing.
 *
 * Pemakaian:
 *   php artisan dlh:cleanup-orphan-files                 (dry-run, lihat daftar)
 *   php artisan dlh:cleanup-orphan-files --delete        (benar-benar hapus)
 *   php artisan dlh:cleanup-orphan-files --days=7        (grace period 7 hari)
 */
class CleanupOrphanFiles extends Command
{
    protected $signature = 'dlh:cleanup-orphan-files
        {--dry-run : Hanya tampilkan file yatim yang terdeteksi, tanpa menghapus}
        {--delete : Benar-benar hapus file yatim (default perintah ini adalah dry-run)}
        {--disk=public : Disk yang dipindai}
        {--days=2 : Grace period — hanya proses file yang lebih tua dari N hari}';

    protected $description = 'Deteksi dan hapus file di storage (B2) yang sudah tidak memiliki referensi di database';

    /**
     * Prefix folder sistem yang dikecualikan dari pemindaian.
     * Masing-masing sudah punya mekanisme pembersihan sendiri:
     * - livewire-tmp : command dlh:cleanup-b2-orphans
     * - backups      : dikelola fitur backup/restore (backup baru menghapus yang lama)
     * - temp         : staging impor GIS (dihapus setelah diproses)
     * - gis-shp      : arsip file sumber impor GIS (.shp/.zip) — sengaja
     *                  dipertahankan sebagai cadangan re-import & ikut backup
     */
    protected const EXCLUDED_PREFIXES = [
        'livewire-tmp/',
        'backups/',
        'temp/',
        'gis-shp/',
    ];

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $delete = (bool) $this->option('delete') && ! $this->option('dry-run');
        $graceDays = max(0, (int) $this->option('days'));

        $disk = Storage::disk($diskName);

        // 1. Kumpulkan seluruh referensi file dari database.
        [$references, $failed] = $this->collectReferences();

        if ($failed !== []) {
            $this->warn('Gagal membaca '.count($failed).' sumber referensi: '.implode(', ', $failed));
        }

        if ($references === []) {
            $this->error('Tidak ada satu pun referensi file yang bisa dibaca dari database. Penghapusan dibatalkan demi keamanan.');

            return 1;
        }

        $this->info('Referensi file dari database: '.count($references).' path unik.');

        // 2. Daftar seluruh objek di disk (lazy, ter-paginasi).
        try {
            $objects = $disk->listContents('', true)->filter(fn ($item) => $item->isFile());
        } catch (Throwable $e) {
            $this->error('Gagal membaca daftar file di disk "'.$diskName.'": '.$e->getMessage());

            return 1;
        }

        $cutoff = now()->subDays($graceDays)->timestamp;
        $total = 0;
        $orphans = [];

        foreach ($objects as $object) {
            $total++;
            $path = $object->path();

            if ($this->isExcluded($path)) {
                continue;
            }

            // Grace period: file yang baru diunggah mungkin masih diproses
            // (mis. antrean upload gambar pengaduan) — jangan disentuh.
            $lastModified = $object->lastModified();

            if ($lastModified !== null && $lastModified >= $cutoff) {
                continue;
            }

            if (! isset($references[$this->normalizePath($path)])) {
                $orphans[] = $path;
            }
        }

        $this->info('Total objek dipindai: '.$total.' (di luar folder sistem & grace period '.$graceDays.' hari).');

        if ($orphans === []) {
            $this->info('Tidak ada file yatim — storage bersih.');

            return 0;
        }

        $this->warn('Terdeteksi '.count($orphans).' file yatim (tanpa referensi di database):');

        foreach (array_slice($orphans, 0, 100) as $path) {
            $this->line('  - '.$path);
        }

        if (count($orphans) > 100) {
            $this->line('  ... dan '.(count($orphans) - 100).' lainnya.');
        }

        if (! $delete) {
            $this->info('Mode dry-run (default). Jalankan dengan --delete untuk benar-benar menghapus.');

            return 0;
        }

        // 3. Hapus lewat service terpusat agar versi lama objek di B2
        //    (versioning: delete biasa hanya membuat hide marker) ikut ter-purge.
        $files = app(FileUploadService::class);
        $deleted = 0;

        foreach ($orphans as $path) {
            try {
                $files->deletePath($path, $diskName);
                $deleted++;
            } catch (Throwable $e) {
                $this->warn('Gagal menghapus '.$path.': '.$e->getMessage());
            }
        }

        $this->info('Selesai. '.$deleted.' dari '.count($orphans).' file yatim dihapus (termasuk versi lama di bucket).');

        return 0;
    }

    /**
     * Kumpulkan set referensi path dari SEMUA kolom file di database.
     * Setiap query dibungkus try/catch agar satu tabel bermasalah tidak
     * menggagalkan keseluruhan — namun bila semuanya gagal (mis. database
     * tidak reachable), hasil kosong membuat mode delete ditolak.
     *
     * @return array{0: array<string, bool>, 1: list<string>}
     */
    protected function collectReferences(): array
    {
        $references = [];
        $failed = [];

        // model => kolom-kolom penyimpan path file.
        $sources = [
            [User::class, ['photo_path']],
            [Artikel::class, ['thumbnail']],
            [PengaduanPengendalianFoto::class, ['path_foto']],
            [PengaduanSampahFoto::class, ['path_foto']],
            [PengaduanRthFoto::class, ['path_foto']],
            [PengaduanTataPenataanFoto::class, ['path_foto']],
            [PermohonanDokumen::class, ['path_dokumen']],
            [PelanggaranMedia::class, ['path']],
            [SosialisasiFile::class, ['path']],
            [SosialisasiPeserta::class, ['sertifikat_path']],
            [Sanksi::class, ['surat_path']],
            [SidakMedia::class, ['path']],
            [ObjekPengawasanDokumen::class, ['file_path']],
            [PermohonanRekomendasi::class, ['surat_permohonan']],
            [PermohonanPinjamTaman::class, ['surat_permohonan', 'surat_jaminan']],
            [PengajuanRintekPertek::class, [
                'surat_permohonan', 'dplh_ukl_upl', 'nib', 'sppl', 'denah_tps_lb3', 'sop_tanggap_darurat',
            ]],
            [DataTanamPohon::class, ['foto_dokumentasi']],
        ];

        foreach ($sources as [$model, $columns]) {
            foreach ($columns as $column) {
                try {
                    $values = $model::query()->whereNotNull($column)->pluck($column)->all();

                    foreach ($values as $value) {
                        $this->extractPaths($value, $references);
                    }
                } catch (Throwable $e) {
                    $failed[] = class_basename($model).'.'.$column;
                }
            }
        }

        // Gambar yang disematkan di konten artikel (editor Jodit) — path-nya
        // hanya ada di HTML konten, bukan di kolom file tersendiri.
        try {
            $kontenList = Artikel::query()->whereNotNull('konten')->pluck('konten')->all();

            foreach ($kontenList as $konten) {
                if (preg_match_all('~artikel-images(?:/|%2F)[^"\'\s<>?&]+~', (string) $konten, $matches)) {
                    foreach (array_map('urldecode', $matches[0]) as $path) {
                        $references[$this->normalizePath($path)] = true;
                    }
                }
            }
        } catch (Throwable $e) {
            $failed[] = class_basename(Artikel::class).'.konten';
        }

        return [$references, $failed];
    }

    /**
     * Ekstrak path dari nilai kolom: string tunggal, array (cast), atau
     * string JSON mentah (pluck tidak menerapkan cast model).
     */
    protected function extractPaths(mixed $value, array &$references): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->extractPaths($item, $references);
            }

            return;
        }

        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $value = trim($value);

        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $this->extractPaths($decoded, $references);

                return;
            }
        }

        $references[$this->normalizePath($value)] = true;
    }

    /**
     * Normalisasi path agar perbandingan referensi vs objek storage konsisten:
     * slash seragam, tanpa slash di depan, dan URL-encoded ter-decode
     * (nama file di storage tidak pernah mengandung '%' — sudah dibersihkan
     * oleh FileUploadService saat upload).
     */
    protected function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        return urldecode($path);
    }

    protected function isExcluded(string $path): bool
    {
        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
