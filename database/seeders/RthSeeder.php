<?php

namespace Database\Seeders;

use App\Models\TamanKota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RthSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTamanKota();
    }

    private function seedTamanKota(): void
    {
        $tamans = [
            [
                'nama' => 'Taman Pusat Kota Palu',
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'luas' => 5000.50,
                'foto' => $this->copyDummyImage('taman1.jpg'),
                'fasilitas' => 'Jogging track, bangku taman, lampu taman, toilet, area bermain anak',
            ],
            [
                'nama' => 'Taman Vatulemo',
                'latitude' => -0.9045,
                'longitude' => 119.8765,
                'luas' => 3500.75,
                'foto' => $this->copyDummyImage('taman2.jpg'),
                'fasilitas' => 'Kolam ikan, gazebo, area olahraga, tempat duduk',
            ],
            [
                'nama' => 'Taman Nosarara Nosabatutu',
                'latitude' => -0.8912,
                'longitude' => 119.8650,
                'luas' => 8000.00,
                'foto' => $this->copyDummyImage('taman3.jpg'),
                'fasilitas' => 'Amphitheater, taman bunga, track lari, area piknik',
            ],
            [
                'nama' => 'Taman Kelurahan Birobuli',
                'latitude' => -0.8823,
                'longitude' => 119.8550,
                'luas' => 2500.00,
                'foto' => $this->copyDummyImage('taman4.jpg'),
                'fasilitas' => 'Area bermain anak, bangku taman, lampu penerangan',
            ],
            [
                'nama' => 'Taman Kelurahan Lere',
                'latitude' => -0.9100,
                'longitude' => 119.8800,
                'luas' => 3000.50,
                'foto' => $this->copyDummyImage('taman5.jpg'),
                'fasilitas' => 'Lapangan olahraga kecil, gazebo, toilet umum',
            ],
        ];

        foreach ($tamans as $taman) {
            TamanKota::create($taman);
        }
    }

    private function copyDummyImage(string $filename): string
    {
        $sourcePath = storage_path("app/public/seeder-images/{$filename}");
        
        if (!File::exists($sourcePath)) {
            return 'placeholder.jpg';
        }

        $destinationPath = 'rth/' . $filename;
        Storage::disk('public')->copy("seeder-images/{$filename}", $destinationPath);
        
        return $destinationPath;
    }
}
