<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageUploadService
{
    /**
     * Proses satu gambar sumber: orientasi EXIF diterapkan, diperkecil bila
     * melebihi 1920px, lalu dikonversi ke WebP (metadata EXIF/GPS terhapus
     * demi privasi) dan disimpan ke disk tujuan dengan NAMA ASLI file
     * (hanya ekstensi yang disesuaikan), langsung di folder konteks
     * — cukup SATU file, tanpa folder varian (mis.
     * pengaduan-pengendalian/foto-bukti.webp).
     *
     * Bila runtime tidak mendukung encoder WebP, otomatis jatuh ke JPEG lalu
     * PNG; bila format sumber sama sekali tidak terbaca (mis. HEIC tanpa
     * decoder), file asli disimpan apa adanya sehingga lampiran tidak hilang.
     *
     * Bila sudah ada file dengan nama yang sama, ditambahkan sufiks -1, -2,
     * dst agar file lama tidak tertimpa.
     *
     * @param  string  $sourcePath  Absolute path to a local image file.
     * @param  string  $context     Storage folder, e.g. "pengaduan-pengendalian".
     * @param  string  $disk        Laravel disk name (defaults to "public").
     * @return string Path relatif terhadap root disk.
     */
    public function upload(string $sourcePath, string $context, string $disk = 'public'): string
    {
        $baseName = $this->safeBaseName(basename($sourcePath));
        $context = trim($context, '/');

        $encoded = $this->encodeImage($sourcePath);

        $fileName = $this->uniqueName($baseName.'.'.$encoded['extension'], $context, $disk);
        $path = ($context !== '' ? $context.'/' : '').$fileName;

        $written = Storage::disk($disk)->put($path, $encoded['binary']);

        // Disk dikonfigurasi dengan throw=false sehingga kegagalan put()
        // bersifat sunyi. Lempar exception agar proses menandai foto
        // sebagai 'failed' dan mencoba ulang, bukan mencatat path yang
        // sebenarnya tidak pernah tertulis ke storage.
        if ($written === false) {
            throw new RuntimeException('Gagal menulis gambar ke storage: '.$path);
        }

        return $path;
    }

    /**
     * Encode gambar sumber dengan rantai fallback yang aman terhadap
     * kapabilitas PHP di server:
     *   WebP -> JPEG (latar putih) -> PNG -> file asli apa adanya.
     *
     * @return array{binary:string, extension:string}
     */
    protected function encodeImage(string $sourcePath): array
    {
        $passthrough = fn (): array => [
            'binary' => (string) file_get_contents($sourcePath),
            'extension' => strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'jpg',
        ];

        $driverClass = extension_loaded('imagick')
            ? ImagickDriver::class
            : GdDriver::class;

        $manager = new ImageManager(new $driverClass);

        try {
            $image = $manager->read($sourcePath);

            // Apply EXIF orientation (rotates pixels), then strip metadata on re-encode.
            try {
                $image->orient();
            } catch (Throwable) {
                // orientation not available — continue without it
            }

            // Perkecil hanya bila melebihi 1920px (hemat ukuran tanpa merusak kualitas umum).
            $image->scaleDown(width: 1920);
        } catch (Throwable $e) {
            // Format sumber tidak dapat dibaca driver gambar (mis. HEIC pada
            // build GD tanpa decoder). Jangan biarkan unggahan gagal total —
            // simpan file asli apa adanya (privasi EXIF tidak bisa dipastikan).
            Log::warning('Photo source could not be decoded; storing the original file.', [
                'file' => basename($sourcePath),
                'error' => $e->getMessage(),
            ]);

            return $passthrough();
        }

        // WebP kualitas tinggi (85) agar tampilan tetap setara dengan aslinya.
        // Tidak semua build PHP punya imagewebp() — cek dulu sebelum memakai.
        if (extension_loaded('imagick') || function_exists('imagewebp')) {
            try {
                return [
                    'binary' => (string) $image->toWebp(85),
                    'extension' => 'webp',
                ];
            } catch (Throwable $e) {
                Log::warning('WebP encoding failed; falling back to JPEG.', [
                    'file' => basename($sourcePath),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // JPEG tidak menyimpan transparansi — ratakan dulu di atas kanvas
        // putih agar area transparan tidak menjadi hitam.
        try {
            $flattened = $manager
                ->create($image->width(), $image->height())
                ->fill('#ffffff')
                ->place($image);

            return [
                'binary' => (string) $flattened->toJpeg(85),
                'extension' => 'jpg',
            ];
        } catch (Throwable $e) {
            Log::warning('JPEG encoding failed; falling back to PNG.', [
                'file' => basename($sourcePath),
                'error' => $e->getMessage(),
            ]);
        }

        try {
            return [
                'binary' => (string) $image->toPng(),
                'extension' => 'png',
            ];
        } catch (Throwable $e) {
            Log::warning('PNG encoding failed; storing the original file.', [
                'file' => basename($sourcePath),
                'error' => $e->getMessage(),
            ]);

            return $passthrough();
        }
    }

    /**
     * Nama dasar dari nama file asli, dibersihkan dari karakter yang
     * berbahaya bagi filesystem/URL. Spasi dan karakter unicode dipertahankan.
     */
    protected function safeBaseName(string $fileName): string
    {
        $base = (string) pathinfo($fileName, PATHINFO_FILENAME);
        $base = str_replace(chr(0), '', $base);
        $base = preg_replace('~[<>:"/\\\\|?*#%\r\n\t]~', '-', $base) ?? '';
        $base = trim($base, " \t.-_");

        return $base === '' ? 'foto' : $base;
    }

    /**
     * Pastikan nama file unik di folder tujuan. Bila sudah ada file dengan
     * nama yang sama, tambahkan sufiks -1, -2, dst agar file lama tidak
     * tertimpa.
     */
    protected function uniqueName(string $filename, string $context, string $disk): string
    {
        $storage = Storage::disk($disk);
        $dir = $context !== '' ? $context.'/' : '';

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
}
