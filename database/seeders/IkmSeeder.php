<?php

namespace Database\Seeders;

use App\Models\IkmResponse;
use Illuminate\Database\Seeder;

class IkmSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 50 responses IKM dengan nilai random antara 1-4
        for ($i = 1; $i <= 50; $i++) {
            IkmResponse::create([
                'indikator_1' => rand(2, 4), // Prosedur dan Persyaratan
                'indikator_2' => rand(2, 4), // Kecepatan Waktu Petugas
                'indikator_3' => rand(2, 4), // Biaya dan Tarif
                'indikator_4' => rand(2, 4), // Kualitas Sarana & Prasarana
                'indikator_5' => rand(2, 4), // Kompetensi dan Perilaku Petugas
                'indikator_6' => rand(2, 4), // Penanganan Pengaduan
                'indikator_7' => rand(2, 4), // Hasil Layanan (Produk)
                'saran' => $this->getRandomSaran(),
            ]);
        }
    }

    private function getRandomSaran(): ?string
    {
        $sarans = [
            'Pelayanan sudah baik, pertahankan',
            'Mohon ditingkatkan kecepatan pelayanannya',
            'Petugas ramah dan profesional',
            'Perlu penambahan fasilitas di kantor',
            'Prosedur sudah jelas dan mudah dipahami',
            'Waktu tunggu terlalu lama',
            'Sistem online sangat membantu',
            'Mohon lebih banyak sosialisasi tentang layanan',
            null, // Beberapa tanpa saran
            null,
        ];

        return $sarans[array_rand($sarans)];
    }
}
