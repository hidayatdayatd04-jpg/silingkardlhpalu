<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class DownloadArtikelImages extends Command
{
    protected $signature = 'dlh:download-images';
    protected $description = 'Download gambar dari website DLH Sulteng untuk artikel';

    private array $imageUrls = [
        // Page 1 - dari website DLH Sulteng
        'https://dlh.sultengprov.go.id/img/informasi/berita/1770171407_258c4ec0b24d3b7a51e8.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758609074_3f3be49187a069d17e29.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758609535_d868768ae56e87e7bab2.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758607983_6a4d5bc6a6216d632cb3.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758608216_16de0f778d43659ab8de.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758608465_cd1e26fc028518953ec2.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758599573_af4a32fcfb54a24894de.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1758599861_56d04b423eb3e256b36a.jpeg',
        // Page 2
        'https://dlh.sultengprov.go.id/img/informasi/berita/1757654092_959a50b1f3ad383ba5ac.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1757402634_a4119a1ae26ece569b1c.png',
        // Page 3
        'https://dlh.sultengprov.go.id/img/informasi/berita/1756689123_39ce8a4c4cb4cac7f978.jpeg',
        'https://dlh.sultengprov.go.id/img/informasi/berita/1756689241_a51c3fc08f226be97877.jpeg',
    ];

    public function handle(): int
    {
        $this->info('==============================================');
        $this->info('  DOWNLOAD GAMBAR ARTIKEL DLH PALU');
        $this->info('==============================================');
        $this->newLine();

        $destinationFolder = storage_path('app/public/admin/artikel');

        if (!File::exists($destinationFolder)) {
            File::makeDirectory($destinationFolder, 0755, true);
        }

        $downloadedFiles = [];

        foreach ($this->imageUrls as $index => $url) {
            $filename = 'artikel_' . ($index + 1) . '_' . basename($url);
            $destinationPath = $destinationFolder . '/' . $filename;

            $this->info('[' . ($index + 1) . '/' . count($this->imageUrls) . '] Downloading: ' . basename($url));

            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    File::put($destinationPath, $response->body());
                    $size = filesize($destinationPath);
                    $this->info('  ✓ Saved: ' . $filename . ' (' . $size . ' bytes)');
                    $downloadedFiles[] = 'admin/artikel/' . $filename;
                } else {
                    $this->error('  ✗ Failed (HTTP ' . $response->status() . '): ' . $url);
                    $downloadedFiles[] = null;
                }
            } catch (\Exception $e) {
                $this->error('  ✗ Error: ' . $e->getMessage());
                $downloadedFiles[] = null;
            }
        }

        $this->newLine();
        $this->info('Download selesai. Total file berhasil: ' . count(array_filter($downloadedFiles)));
        $this->newLine();

        // Update ArtikelSeeder
        $this->updateSeeder($downloadedFiles);

        return 0;
    }

    private function updateSeeder(array $downloadedFiles): void
    {
        $this->info('🔄 Memperbarui ArtikelSeeder.php...');

        $seederPath = base_path('database/seeders/ArtikelSeeder.php');
        $content = File::get($seederPath);

        // Build new thumbnail array mapping
        $validFiles = array_filter($downloadedFiles);
        $thumbnailArray = array_values($validFiles);

        $this->info('File gambar tersedia: ' . count($thumbnailArray));

        // Replace the copyThumbnail method to use real images
        $newMethod = $this->buildNewCopyThumbnailMethod($thumbnailArray);

        // Replace old copyThumbnail method
        $pattern = '/private function copyThumbnail\(\): string\s*\{.*?return \'placeholder\.jpg\';\s*\}/s';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $newMethod, $content);
            File::put($seederPath, $content);
            $this->info('✓ ArtikelSeeder.php berhasil diperbarui!');
        } else {
            $this->warn('⚠ Pattern tidak ditemukan, mencoba replace manual...');
            $this->updateSeederManual($content, $seederPath, $thumbnailArray);
        }
    }

    private function updateSeederManual(string $content, string $seederPath, array $thumbnailArray): void
    {
        $thumbnailsPhp = var_export($thumbnailArray, true);

        $newMethod = <<<PHP
    private function copyThumbnail(): string
    {
        \$thumbnails = {$thumbnailsPhp};

        \$destinationFolder = storage_path('app/public/admin/artikel');

        if (! File::exists(\$destinationFolder)) {
            File::makeDirectory(\$destinationFolder, 0755, true);
        }

        // Pick a random thumbnail
        \$filename = \$thumbnails[array_rand(\$thumbnails)];
        \$destinationPath = storage_path('app/public/' . \$filename);

        if (File::exists(\$destinationPath)) {
            return \$filename;
        }

        return 'placeholder.jpg';
    }
PHP;

        // Find and replace the old copyThumbnail method
        $startMarker = '    private function copyThumbnail(): string';
        $endMarker = "        return 'placeholder.jpg';\n    }";

        $startPos = strpos($content, $startMarker);
        $endPos = strpos($content, $endMarker);

        if ($startPos !== false && $endPos !== false) {
            $endPos += strlen($endMarker);
            $content = substr_replace($content, $newMethod, $startPos, $endPos - $startPos);
            File::put($seederPath, $content);
            $this->info('✓ ArtikelSeeder.php berhasil diperbarui (manual)!');
        } else {
            $this->error('✗ Gagal memperbarui ArtikelSeeder.php');
        }
    }

    private function buildNewCopyThumbnailMethod(array $thumbnailArray): string
    {
        $thumbnailsPhp = var_export($thumbnailArray, true);

        return <<<PHP
    private function copyThumbnail(): string
    {
        \$thumbnails = {$thumbnailsPhp};

        \$destinationFolder = storage_path('app/public/admin/artikel');

        if (! File::exists(\$destinationFolder)) {
            File::makeDirectory(\$destinationFolder, 0755, true);
        }

        // Pick a random thumbnail
        \$filename = \$thumbnails[array_rand(\$thumbnails)];
        \$destinationPath = storage_path('app/public/' . \$filename);

        if (File::exists(\$destinationPath)) {
            return \$filename;
        }

        return 'placeholder.jpg';
    }
PHP;
    }
}
