<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupStorageSeeder extends Seeder
{
    /**
     * Setup folder structure untuk storage
     */
    public function run(): void
    {
        $this->info('Setting up storage folders...');
        
        $folders = [
            'admin/objek-pengawasan',
            'admin/pengaduan-tata-penataan',
            'admin/sidak',
            'admin/pelanggaran',
            'admin/sanksi',
            'admin/sosialisasi',
            'admin/perizinan-tebang-pohon',
            'admin/data-tanam-pohon',
            'admin/pinjam-taman',
            'admin/permohonan-rekomendasi',
            'admin/pengajuan-rintek-pertek',
            'admin/pengendalian',
            'admin/rth',
            'admin/sampah',
            'rth',
            'sampah',
            'seeder-images',
            'seeder-documents',
        ];

        foreach ($folders as $folder) {
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
                $this->info("Created folder: {$folder}");
            }
        }

        $this->info('Storage folders setup completed!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Copy your images to: storage/app/public/seeder-images/');
        $this->info('2. Copy your documents to: storage/app/public/seeder-documents/');
        $this->info('3. Run: php artisan storage:link');
        $this->info('4. Run: php artisan db:seed');
    }

    private function info(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
