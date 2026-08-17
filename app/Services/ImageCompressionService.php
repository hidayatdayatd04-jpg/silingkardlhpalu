<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Kompresi gambar untuk upload admin (foto bukti pengaduan via form admin).
 *
 * Delegasi penuh ke FileUploadService agar seluruh upload gambar di aplikasi
 * memakai satu jalur yang sama: kompres + konversi ke WebP (kualitas tinggi),
 * orientasi EXIF diterapkan, metadata dibuang, dan file sementara dibersihkan.
 */
class ImageCompressionService
{
    public function __construct(protected FileUploadService $files)
    {
    }

    public function compressAndStore(UploadedFile $file, string $directory = 'laporan'): string|false
    {
        return $this->files->store($file, $directory, 'public');
    }
}
