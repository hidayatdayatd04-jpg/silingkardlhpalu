<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupSeederCommand extends Command
{
    protected $signature = 'dlh:setup-seeder {--fresh : Reset database sebelum seeding}';
    
    protected $description = 'Setup lengkap seeder untuk DLH Palu (folder + placeholder + seeding)';

    public function handle(): int
    {
        $this->info('==============================================');
        $this->info('   SETUP SEEDER DLH PALU');
        $this->info('==============================================');
        $this->newLine();

        // Step 1: Setup Storage Folders
        $this->info('📁 Step 1: Membuat struktur folder storage...');
        Artisan::call('db:seed', ['--class' => 'SetupStorageSeeder']);
        $this->line(Artisan::output());

        // Step 2: Create Placeholder Files
        $this->info('🖼️  Step 2: Membuat file placeholder...');
        Artisan::call('db:seed', ['--class' => 'CreatePlaceholderFilesSeeder']);
        $this->line(Artisan::output());

        // Step 3: Create Storage Link
        $this->info('🔗 Step 3: Membuat symbolic link storage...');
        try {
            Artisan::call('storage:link');
            $this->info('✓ Symbolic link berhasil dibuat');
        } catch (\Exception $e) {
            $this->warn('⚠ Symbolic link sudah ada atau gagal dibuat: ' . $e->getMessage());
        }
        $this->newLine();

        // Step 4: Seeding Data
        if ($this->option('fresh')) {
            $this->warn('⚠️  Mode FRESH: Database akan di-reset!');
            if ($this->confirm('Apakah Anda yakin ingin melanjutkan?', true)) {
                $this->info('🔄 Step 4: Melakukan migration fresh dan seeding...');
                Artisan::call('migrate:fresh', ['--seed' => true]);
                $this->line(Artisan::output());
            } else {
                $this->error('Setup dibatalkan.');
                return 1;
            }
        } else {
            $this->info('📝 Step 4: Menjalankan seeder...');
            Artisan::call('db:seed');
            $this->line(Artisan::output());
        }

        // Success Message
        $this->newLine();
        $this->info('==============================================');
        $this->info('   ✅ SETUP SEEDER BERHASIL!');
        $this->info('==============================================');
        $this->newLine();
        
        $this->table(
            ['Username', 'Password', 'Role'],
            [
                ['superadmin', 'superadmin123', 'Superadmin'],
                ['pengendalian', 'pengendalian123', 'Admin Pengendalian'],
                ['sampah-lb3', 'sampah123', 'Admin Sampah & LB3'],
                ['tata-penataan', 'tata123', 'Admin Tata Penataan'],
                ['rth', 'rth123', 'Admin RTH'],
            ]
        );

        $this->newLine();
        $this->info('📋 Langkah Selanjutnya:');
        $this->line('1. (Opsional) Copy file gambar ke: storage/app/public/seeder-images/');
        $this->line('2. (Opsional) Copy file dokumen ke: storage/app/public/seeder-documents/');
        $this->line('3. Login ke admin panel dengan salah satu user di atas');
        $this->line('4. Verifikasi data sudah muncul di semua menu admin');
        $this->newLine();
        
        $this->info('📖 Dokumentasi lengkap: PANDUAN_SEEDER.md');
        
        return 0;
    }
}
