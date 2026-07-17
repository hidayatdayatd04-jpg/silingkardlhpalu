<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    public function compressAndStore(UploadedFile $file, string $directory = 'laporan'): string
    {
        // Check if GD or Imagick extension is available
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            // Fallback: just store the file without compression
            return $file->store($directory, 'public');
        }

        try {
            // Try to use Intervention Image with available driver
            $driverClass = extension_loaded('imagick') 
                ? \Intervention\Image\Drivers\Imagick\Driver::class 
                : \Intervention\Image\Drivers\Gd\Driver::class;
            
            $manager = new \Intervention\Image\ImageManager(new $driverClass);
            $image = $manager->read($file->getRealPath());

            // Scale down to max 1200x1200
            $image->scaleDown(width: 1200, height: 1200);

            // Encode with quality 80
            $quality = 80;
            $encoded = $image->encode(new \Intervention\Image\Encoders\JpegEncoder($quality));

            // Reduce quality if file size > 500KB
            while (strlen((string) $encoded) > 500 * 1024 && $quality > 20) {
                $quality -= 10;
                $encoded = $image->encode(new \Intervention\Image\Encoders\JpegEncoder($quality));
            }

            $filename = Str::uuid().'.jpg';
            $path = $directory.'/'.$filename;
            Storage::disk('public')->put($path, (string) $encoded);

            return $path;
        } catch (\Exception $e) {
            // Fallback on any error: just store the file
            return $file->store($directory, 'public');
        }
    }
}
