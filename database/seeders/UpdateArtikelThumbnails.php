<?php

namespace Database\Seeders;

use App\Models\Artikel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UpdateArtikelThumbnails extends Seeder
{
    private array $thumbnails = [
        'admin/artikel/artikel_1_1770171407_258c4ec0b24d3b7a51e8.jpeg',
        'admin/artikel/artikel_2_1758609074_3f3be49187a069d17e29.jpeg',
        'admin/artikel/artikel_3_1758609535_d868768ae56e87e7bab2.jpeg',
        'admin/artikel/artikel_4_1758607983_6a4d5bc6a6216d632cb3.jpeg',
        'admin/artikel/artikel_5_1758608216_16de0f778d43659ab8de.jpeg',
        'admin/artikel/artikel_6_1758608465_cd1e26fc028518953ec2.jpeg',
        'admin/artikel/artikel_7_1758599573_af4a32fcfb54a24894de.jpeg',
        'admin/artikel/artikel_8_1758599861_56d04b423eb3e256b36a.jpeg',
        'admin/artikel/artikel_9_1757654092_959a50b1f3ad383ba5ac.jpeg',
        'admin/artikel/artikel_10_1757402634_a4119a1ae26ece569b1c.png',
        'admin/artikel/artikel_11_1756689123_39ce8a4c4cb4cac7f978.jpeg',
        'admin/artikel/artikel_12_1756689241_a51c3fc08f226be97877.jpeg',
    ];

    public function run(): void
    {
        $this->command->info('Updating article thumbnails...');

        $artikels = Artikel::all();
        $count = $artikels->count();

        $this->command->info("Found {$count} articles in database.");

        foreach ($artikels as $index => $artikel) {
            $thumbIndex = $index % count($this->thumbnails);
            $newThumbnail = $this->thumbnails[$thumbIndex];

            $oldThumb = $artikel->thumbnail ?? 'NULL';
            $artikel->thumbnail = $newThumbnail;
            $artikel->save();

            $this->command->info("[{$artikel->id}] {$artikel->judul}");
            $this->command->info("  Old: {$oldThumb}");
            $this->command->info("  New: {$newThumbnail}");
        }

        $this->command->info("Done! Updated {$count} articles with new thumbnails.");
    }
}
