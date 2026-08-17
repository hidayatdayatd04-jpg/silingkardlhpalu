<?php

namespace App\Services;

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
     * (hanya ekstensi yang diganti menjadi .webp), langsung di folder konteks
     * — cukup SATU file, tanpa folder varian (mis.
     * pengaduan-pengendalian/foto-bukti.webp).
     *
     * Bila sudah ada file dengan nama yang sama, ditambahkan sufiks -1, -2,
     * dst agar file lama tidak tertimpa.
     *
     * @param  string  $sourcePath  Absolute path to a local image file.
     * @param  string  $context     Storage folder, e.g. "pengaduan-pengendalian".
     * @param  string  $disk        Laravel disk name (defaults to "public" -> B2).
     * @return string Path relatif terhadap root disk.
     */
    public function upload(string $sourcePath, string $context, string $disk = 'public'): string
    {
        $driverClass = extension_loaded('imagick')
            ? ImagickDriver::class
            : GdDriver::class;

        $manager = new ImageManager(new $driverClass);

        $image = $manager->read($sourcePath);

        // Apply EXIF orientation (rotates pixels), then strip metadata on re-encode.
        try {
            $image->orient();
        } catch (Throwable $e) {
            // orientation not available — continue without it
        }

        // Perkecil hanya bila melebihi 1920px (hemat ukuran tanpa merusak kualitas umum).
        $image->scaleDown(width: 1920);

        // WebP kualitas tinggi (85) agar tampilan tetap setara dengan aslinya.
        $encoded = $image->toWebp(85);

        // Pertahankan nama asli file (hanya ekstensi diganti .webp).
        $baseName = $this->safeBaseName(basename($sourcePath));
        $context = trim($context, '/');
        $fileName = $this->uniqueName($baseName.'.webp', $context, $disk);
        $path = ($context !== '' ? $context.'/' : '').$fileName;

        $written = Storage::disk($disk)->put($path, (string) $encoded);

        // Disk dikonfigurasi dengan throw=false sehingga kegagalan put()
        // bersifat sunyi. Lempar exception agar job queue menandai foto
        // sebagai 'failed' dan mencoba ulang, bukan mencatat path yang
        // sebenarnya tidak pernah tertulis ke storage.
        if ($written === false) {
            throw new RuntimeException('Gagal menulis gambar ke storage: '.$path);
        }

        return $path;
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
