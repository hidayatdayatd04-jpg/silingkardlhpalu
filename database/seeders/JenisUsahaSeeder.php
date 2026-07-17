<?php

namespace Database\Seeders;

use App\Models\JenisUsaha;
use Illuminate\Database\Seeder;

class JenisUsahaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Rumah Makan', 'Bengkel', 'Pabrik', 'Perkebunan', 'Hotel'] as $nama) {
            JenisUsaha::firstOrCreate(['nama' => $nama]);
        }
    }
}
