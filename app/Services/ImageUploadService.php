<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Throwable;

class ImageUploadService
{
    /**
     * Generate thumb (300px), medium (800px) and full (1920px) WebP variants
     * from a single source image and store them on the given disk, grouped
     * under a UUID folder (e.g. pengaduan/{uuid}/thumb.webp).
     *
     * Re-encoding to WebP strips all EXIF/GPS/device metadata (privacy safe).
     * Orientation is applied from EXIF before stripping it.
     *
     * @param  string  $sourcePath  Absolute path to a local image file.
     * @param  string  $context     Storage folder, e.g. "pengaduan-pengendalian".
     * @param  string  $disk        Laravel disk name (defaults to "public" -> B2).
     * @return array{thumb:string,medium:string,full:string}
     */
    public function upload(string $sourcePath, string $context, string $disk = 'public'): array
    {
        $driverClass = extension_loaded('imagick')
            ? ImagickDriver::class
            : GdDriver::class;

        $manager = new ImageManager(new $driverClass);

        $sizes = [
            'thumb' => 300,
            'medium' => 800,
            'full' => 1920,
        ];

        $folder = rtrim($context, '/').'/'.Str::uuid()->toString();
        $paths = [];

        foreach ($sizes as $name => $width) {
            $image = $manager->read($sourcePath);

            // Apply EXIF orientation (rotates pixels), then strip metadata on re-encode.
            try {
                $image->orient();
            } catch (Throwable $e) {
                // orientation not available — continue without it
            }

            $image->scaleDown(width: $width);

            // toWebp(80) re-encodes and drops EXIF/GPS metadata by default.
            $encoded = $image->toWebp(80);

            $path = $folder.'/'.$name.'.webp';
            Storage::disk($disk)->put($path, (string) $encoded);

            $paths[$name] = $path;
        }

        return $paths;
    }
}
