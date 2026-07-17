<?php

namespace Database\Seeders;

use App\Models\JenisUsaha;
use App\Models\JenisLb3;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedJenisUsaha();
        $this->seedJenisLb3();
    }

    private function seedJenisUsaha(): void
    {
        $jenisUsaha = [
            'Rumah Makan',
            'Bengkel',
            'Pabrik',
            'Perkebunan',
            'Hotel',
            'Restoran',
            'Rumah Sakit',
            'Puskesmas',
            'Klinik',
            'Apotek',
            'Laboratorium',
            'Bengkel Motor',
            'Bengkel Mobil',
            'Cuci Motor',
            'Industri Makanan',
            'Industri Minuman',
            'Industri Tekstil',
            'Industri Plastik',
            'Industri Kimia',
            'Pertambangan',
        ];

        foreach ($jenisUsaha as $nama) {
            JenisUsaha::firstOrCreate(['nama' => $nama]);
        }
    }

    private function seedJenisLb3(): void
    {
        $jenisLb3 = [
            'Medis',
            'Oli Bekas',
            'Kimia',
            'Aki',
            'DLL',
        ];

        foreach ($jenisLb3 as $nama) {
            JenisLb3::firstOrCreate(['nama' => $nama]);
        }
    }
}
