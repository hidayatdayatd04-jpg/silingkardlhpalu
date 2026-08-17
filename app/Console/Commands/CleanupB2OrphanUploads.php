<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Membersihkan file upload sementara Livewire yang terlanjur menumpuk di
 * bucket B2 (folder livewire-tmp/).
 *
 * Latar belakang: sebelum perbaikan, disk default aplikasi adalah B2 sehingga
 * setiap upload form Livewire (pengaduan, permohonan, pinjam taman, dll)
 * menaruh salinan sementara di bucket. Livewire TIDAK membersihkan file
 * sementara di disk S3/B2 secara otomatis, sehingga file-file tersebut
 * tertinggal sebagai duplikat/yatim (nama acak 40 karakter, mis. *.jpg).
 *
 * Setelah perbaikan, upload sementara diarahkan ke disk lokal dan dihapus
 * eksplisit setelah diproses, jadi folder ini semestinya kosong. Perintah ini
 * cukup dijalankan sekali untuk membersihkan sisa lama:
 *
 *   php artisan dlh:cleanup-b2-orphans --dry-run   (lihat dulu apa yang dihapus)
 *   php artisan dlh:cleanup-b2-orphans --all       (hapus semua isi livewire-tmp)
 */
class CleanupB2OrphanUploads extends Command
{
    protected $signature = 'dlh:cleanup-b2-orphans
        {--dry-run : Hanya tampilkan file yang akan dihapus, tanpa menghapus}
        {--all : Hapus SEMUA file di folder sementara (default: hanya yang lebih tua dari 24 jam)}';

    protected $description = 'Hapus file upload sementara Livewire yang menumpuk di bucket B2 (livewire-tmp)';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $directory = config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp';

        try {
            $files = $disk->allFiles($directory);
        } catch (Throwable $e) {
            $this->error('Gagal membaca folder "'.$directory.'": '.$e->getMessage());

            return 1;
        }

        if (empty($files)) {
            $this->info('Folder "'.$directory.'" kosong — bucket sudah bersih.');

            return 0;
        }

        $cutoff = now()->subDay()->timestamp;
        $targets = [];

        foreach ($files as $file) {
            if ($this->option('all')) {
                $targets[] = $file;

                continue;
            }

            try {
                if ($disk->lastModified($file) < $cutoff) {
                    $targets[] = $file;
                }
            } catch (Throwable $e) {
                // File mungkin sudah dihapus proses lain — lewati.
            }
        }

        if (empty($targets)) {
            $this->info('Ditemukan '.count($files).' file, namun tidak ada yang lebih tua dari 24 jam. Gunakan --all untuk menghapus semuanya.');

            return 0;
        }

        $this->info('Ditemukan '.count($targets).' file sementara yang akan dihapus'.($this->option('all') ? ' (--all)' : ' (usia > 24 jam)').':');

        foreach ($targets as $file) {
            $this->line('  - '.$file);
        }

        if ($this->option('dry-run')) {
            $this->warn('Mode dry-run: tidak ada file yang dihapus.');

            return 0;
        }

        if (! $this->confirm('Lanjutkan menghapus '.count($targets).' file di atas?', true)) {
            $this->warn('Dibatalkan.');

            return 0;
        }

        $deleted = 0;

        $files = app(\App\Services\FileUploadService::class);

        foreach ($targets as $file) {
            try {
                // deletePath() menghapus SELURUH versi objek (data + hide marker)
                // agar file benar-benar hilang dari bucket B2 yang versioning.
                $files->deletePath($file, 'public');
                $deleted++;
            } catch (Throwable $e) {
                $this->warn('Gagal menghapus '.$file.': '.$e->getMessage());
            }
        }

        $this->info('Selesai. '.$deleted.' dari '.count($targets).' file berhasil dihapus.');

        return 0;
    }
}
