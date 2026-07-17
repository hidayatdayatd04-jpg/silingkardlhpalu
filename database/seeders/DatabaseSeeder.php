<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // User & Roles
            RolePermissionSeeder::class,
            
            // Master Data
            ProfilDinasSeeder::class,
            MasterDataSeeder::class,
            
            // RTH (Ruang Terbuka Hijau)
            RthSeeder::class,
            AplikasiRthSeeder::class,

            // Tata Penataan
            TataPenataanSeeder::class,
            
            // Pengendalian
            PengendalianSeeder::class,
            
            // Laporan (Semua Bidang)
            LaporanSeeder::class,

            // SLA Settings
            SlaSettingSeeder::class,
            
            // IKM (Indeks Kepuasan Masyarakat)
            IkmSeeder::class,
            
            // Artikel (Optional - uncomment jika diperlukan)
            // ArtikelSeeder::class,
            

        ]);
    }
}
