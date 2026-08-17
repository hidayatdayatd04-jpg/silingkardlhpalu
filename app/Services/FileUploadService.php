<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Titik tunggal penyimpanan file hasil upload (dipakai seluruh form publik & admin).
 *
 * - Gambar raster (jpg/jpeg/png/webp/bmp/avif) otomatis dikompres dan
 *   dikonversi ke WebP (kualitas tinggi, orientasi EXIF diterapkan, metadata
 *   EXIF/GPS dihapus demi privasi) lalu disimpan dengan NAMA ASLI file
 *   (hanya ekstensi yang diganti menjadi .webp).
 *   GIF dibiarkan apa adanya agar animasi tidak rusak.
 * - File non-gambar (pdf, doc, dll) serta SVG disimpan apa adanya dengan
 *   nama asli.
 * - Bila di direktori tujuan sudah ada file dengan nama yang sama, ditambahkan
 *   sufiks -1, -2, dst agar file lama tidak tertimpa.
 * - File sementara Livewire (TemporaryUploadedFile) dihapus setelah diproses
 *   agar tidak menumpuk sebagai file ganda / yatim di storage (B2).
 * - Bila driver gambar (GD/Imagick) tidak tersedia atau konversi gagal,
 *   file disimpan mentah sebagai fallback (tidak pernah gagal upload).
 */
class FileUploadService
{
    /**
     * Simpan file upload. Gambar raster dikompres + dikonversi ke WebP,
     * file lainnya disimpan apa adanya.
     *
     * @return string Path relatif terhadap root disk (false bila gagal total).
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string|false
    {
        $directory = trim($directory, '/');

        if ($this->isConvertibleImage($file)) {
            $path = $this->storeAsWebp($file, $directory, $disk);

            if ($path !== null) {
                $this->deleteTemporary($file);

                return $path;
            }
        }

        // Non-gambar, SVG, atau konversi gagal -> simpan apa adanya dengan nama asli.
        try {
            $name = $this->uniqueName($this->safeOriginalName($file), $directory, $disk);
            $path = $file->storeAs($directory, $name, ['disk' => $disk]);
        } catch (Throwable $e) {
            // Disk 'public' dikonfigurasi dengan throw=false sehingga kegagalan
            // upload ke B2 bersifat sunyi — catat agar mudah ditelusuri.
            Log::error('FileUploadService: upload gagal (storeAs)', [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        $this->deleteTemporary($file);

        if ($path === false) {
            Log::error('FileUploadService: upload gagal (storage mengembalikan false)', [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory,
                'disk' => $disk,
            ]);

            return false;
        }

        return $path;
    }

    /**
     * Hanya gambar raster yang bisa dikonversi. SVG (vektor) dan file non-gambar
     * dibiarkan apa adanya.
     */
    protected function isConvertibleImage(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        if (! str_starts_with($mime, 'image/')) {
            return false;
        }

        if (in_array($mime, ['image/svg', 'image/svg+xml'], true)) {
            return false;
        }

        // GIF tidak dikonversi agar animasi (bila ada) tidak rusak.
        return in_array($mime, [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
            'image/bmp', 'image/avif',
        ], true);
    }

    /**
     * Kompres + konversi gambar ke WebP dan simpan ke disk tujuan.
     * Mengembalikan path bila sukses, null bila harus fallback ke penyimpanan mentah.
     */
    protected function storeAsWebp(UploadedFile $file, string $directory, string $disk): ?string
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return null;
        }

        try {
            $contents = $this->readContents($file);

            if ($contents === null || $contents === '') {
                return null;
            }

            $driverClass = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;
            $manager = new ImageManager(new $driverClass);

            $image = $manager->read($contents);

            // Terapkan orientasi EXIF (rotasi piksel) sebelum metadata dibuang.
            try {
                $image->orient();
            } catch (Throwable $e) {
                // Orientasi tidak tersedia — lanjutkan tanpa rotasi.
            }

            // Perkecil hanya bila melebihi 1920px (hemat ukuran tanpa merusak kualitas umum).
            $image->scaleDown(width: 1920);

            // WebP kualitas tinggi (85) agar tampilan tetap setara dengan aslinya.
            $encoded = $image->toWebp(85);

            // Pertahankan nama asli file (hanya ekstensi diganti .webp).
            // Bila sudah ada file dengan nama yang sama di tujuan, uniqueName()
            // menambahkan sufiks -1, -2, dst agar tidak menimpa file lama.
            $baseName = pathinfo($this->safeOriginalName($file), PATHINFO_FILENAME);
            $fileName = $this->uniqueName($baseName.'.webp', $directory, $disk);
            $path = ($directory !== '' ? $directory.'/' : '').$fileName;

            $written = Storage::disk($disk)->put($path, (string) $encoded);

            // put() dapat mengembalikan false tanpa melempar exception
            // (throw=false) — jangan laporkan sukses bila file tidak tertulis.
            if ($written === false) {
                Log::error('FileUploadService: gagal menulis file WebP ke storage', [
                    'file' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => $disk,
                ]);

                return null;
            }

            return $path;
        } catch (Throwable $e) {
            // Konversi WebP gagal — fallback penyimpanan mentah tetap dicoba,
            // tetapi catat penyebabnya agar bisa ditelusuri.
            Log::warning('FileUploadService: konversi WebP gagal, fallback ke file mentah', [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Nama asli file yang sudah dibersihkan dari karakter berbahaya bagi
     * filesystem maupun URL (path separator, tanda tanya, persen, dll).
     * Spasi dan karakter unicode dipertahankan agar nama tetap terbaca.
     */
    protected function safeOriginalName(UploadedFile $file): string
    {
        $original = str_replace('\\', '/', (string) $file->getClientOriginalName());
        $original = basename($original);
        $original = str_replace(chr(0), '', $original);

        $extension = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
        $base = (string) pathinfo($original, PATHINFO_FILENAME);

        $base = preg_replace('~[<>:"/\\\\|?*#%\r\n\t]~', '-', $base) ?? '';
        $base = trim($base, " \t.-_");

        if ($base === '') {
            $base = 'file';
        }

        return $extension === '' ? $base : $base.'.'.$extension;
    }

    /**
     * Pastikan nama file unik di direktori tujuan. Bila sudah ada file dengan
     * nama yang sama, tambahkan sufiks -1, -2, dst (nama asli dipertahankan).
     */
    protected function uniqueName(string $filename, string $directory, string $disk): string
    {
        $storage = Storage::disk($disk);
        $dir = $directory !== '' ? $directory.'/' : '';

        $base = (string) pathinfo($filename, PATHINFO_FILENAME);
        $extension = (string) pathinfo($filename, PATHINFO_EXTENSION);

        $candidate = $filename;
        $suffix = 1;

        while ($storage->exists($dir.$candidate)) {
            $candidate = $base.'-'.$suffix.($extension !== '' ? '.'.$extension : '');
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Hapus satu file dari storage (path relatif terhadap root disk).
     * Dipakai saat file diganti atau record dihapus agar tidak menjadi yatim.
     */
    public function deletePath(?string $path, string $disk = 'public'): void
    {
        $this->deleteStoredPath(Storage::disk($disk), $disk, $path);
    }

    /**
     * Hapus daftar file dari storage. Kegagalan per file diabaikan agar tidak
     * mengganggu alur utama (mis. penghapusan record tetap sukses).
     *
     * @param iterable<string|null> $paths
     */
    public function deletePaths(iterable $paths, string $disk = 'public'): void
    {
        $storage = Storage::disk($disk);

        foreach ($paths as $path) {
            $this->deleteStoredPath($storage, $disk, $path);
        }
    }

    /**
     * Hapus satu path dengan validasi keamanan dasar. Kegagalan diabaikan.
     *
     * Untuk disk S3/B2 yang versioning aktif, penghapusan dilakukan dengan
     * melist seluruh versi objek lalu menghapusnya per VersionId (lihat
     * purgeAllVersions) — BUKAN delete() biasa. Alasan: delete() tanpa VersionId
     * hanya menciptakan "hide marker" (file tampak 0 byte / hidden di console B2)
     * sehingga versi lama tetap tersisa. Menghapus per VersionId memastikan
     * file benar-benar hilang secara permanen.
     */
    protected function deleteStoredPath(Filesystem $storage, string $disk, ?string $path): void
    {
        try {
            $path = trim((string) $path);

            // Tolak path kosong, absolut, atau mengandung traversal.
            if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
                return;
            }

            // Hapus seluruh versi (data + delete/hide marker) untuk disk S3/B2.
            $this->purgeAllVersions($disk, $path);

            // Fallback untuk disk non-S3 (lokal) yang tidak mengenal versioning.
            if (($storage->getConfig()['driver'] ?? null) !== 's3' && $storage->exists($path)) {
                $storage->delete($path);
            }
        } catch (Throwable $e) {
            // Abaikan — kegagalan hapus satu file tidak boleh membatalkan operasi.
            Log::warning('FileUploadService: exception saat menghapus file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Hapus SELURUH versi objek (data + delete/hide marker) di bucket S3/B2.
     *
     * Backblaze B2 mengaktifkan versioning secara default. deleteObject TANPA
     * VersionId hanya menambah hide marker sehingga file lama masih muncul di
     * console B2 (walau sudah tidak bisa diunduh). Method ini melist seluruh
     * versi objek (termasuk DeleteMarkers) lalu menghapusnya satu per satu
     * berdasarkan VersionId — menghasilkan penghapusan permanen tanpa menyisakan
     * marker.
     *
     * Pendekatan "list dulu, hapus per VersionId" (tanpa memanggil delete() biasa
     * di awal) penting agar tidak menciptakan hide marker baru yang bisa
     * terlewat oleh konsistensi eventual listObjectVersions.
     *
     * Best-effort: kegagalan hanya dicatat di log, tidak pernah melempar.
     */
    protected function purgeAllVersions(string $disk, string $path): void
    {
        $config = config("filesystems.disks.{$disk}");

        if (($config['driver'] ?? null) !== 's3') {
            return;
        }

        $bucket = $config['bucket'] ?? null;

        if (! $bucket) {
            return;
        }

        try {
            $key = Storage::disk($disk)->path($path);

            $client = new S3Client([
                'credentials' => [
                    'key' => $config['key'] ?? '',
                    'secret' => $config['secret'] ?? '',
                ],
                'region' => $config['region'] ?? 'us-east-1',
                'version' => 'latest',
                'endpoint' => $config['endpoint'] ?? null,
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
            ]);

            $keyMarker = null;
            $versionIdMarker = null;

            // Batasi maksimal 10 halaman (~10.000 versi) agar tidak runaway.
            for ($page = 0; $page < 10; $page++) {
                $result = $client->listObjectVersions([
                    'Bucket' => $bucket,
                    'Prefix' => $key,
                    'KeyMarker' => $keyMarker,
                    'VersionIdMarker' => $versionIdMarker,
                ]);

                $versions = array_merge(
                    $result->get('Versions') ?: [],
                    $result->get('DeleteMarkers') ?: []
                );

                foreach ($versions as $version) {
                    // Prefix bisa ikut match key lain yang berawalan sama
                    // (mis. "uploads/a.jpg" vs "uploads/a.jpg.bak").
                    if (($version['Key'] ?? null) !== $key) {
                        continue;
                    }

                    $params = [
                        'Bucket' => $bucket,
                        'Key' => $version['Key'],
                    ];

                    // Hapus versi spesifik berdasarkan VersionId agar benar-benar
                    // permanen. Bucket non-versioned mengembalikan VersionId null
                    // — cukup hapus tanpa parameter VersionId.
                    if (! empty($version['VersionId']) && $version['VersionId'] !== 'null') {
                        $params['VersionId'] = $version['VersionId'];
                    }

                    $client->deleteObject($params);
                }

                if (! $result->get('IsTruncated')) {
                    break;
                }

                $keyMarker = $result->get('NextKeyMarker');
                $versionIdMarker = $result->get('NextVersionIdMarker');
            }
        } catch (Throwable $e) {
            Log::warning('FileUploadService: gagal membersihkan versi lama file di bucket', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Baca isi biner file upload.
     *
     * Penting: untuk TemporaryUploadedFile, getRealPath()/getContent() menunjuk ke
     * tmpfile kosong — harus dibaca lewat storage aslinya via ->get().
     */
    protected function readContents(UploadedFile $file): ?string
    {
        try {
            $contents = $file->get();

            return ($contents === false || $contents === null || $contents === '') ? null : $contents;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Hapus file sementara Livewire setelah diproses agar tidak menjadi duplikat.
     * Aman dipanggil berulang (bila file sudah dipindahkan, delete hanya mengembalikan false).
     */
    protected function deleteTemporary(UploadedFile $file): void
    {
        try {
            if (! $file instanceof TemporaryUploadedFile) {
                return;
            }

            $file->delete();

            // Di disk lokal Livewire juga menulis file metadata (.json) pendamping —
            // hapus sekalian agar tidak menumpuk (di S3/B2 file ini tidak dibuat).
            try {
                $disk = FileUploadConfiguration::disk();
                $storage = Storage::disk($disk);
                $metaPath = FileUploadConfiguration::path($file->getFilename()).'.json';

                if (($storage->getConfig()['driver'] ?? null) !== 's3' && $storage->exists($metaPath)) {
                    $storage->delete($metaPath);
                }
            } catch (Throwable $e) {
                // Abaikan — pembersihan berkala 24 jam tetap berjalan.
            }
        } catch (Throwable $e) {
            // Abaikan — pembersihan berkala tetap berjalan.
        }
    }
}
