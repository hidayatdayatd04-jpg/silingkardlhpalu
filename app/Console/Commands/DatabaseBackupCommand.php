<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackup;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'app:db-backup';

    protected $description = 'Buat backup database + file upload (.zip) via PDO murni ke disk privat.';

    public function handle(DatabaseBackup $backup): int
    {
        $this->info('Membuat backup database...');

        try {
            $path = $backup->dump();
            $this->info('Backup selesai: '.$path);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
