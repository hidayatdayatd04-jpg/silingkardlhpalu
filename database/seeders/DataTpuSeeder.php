<?php

namespace Database\Seeders;

use App\Models\DataTpu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DataTpuSeeder extends Seeder
{
    public function run(): void
    {
        $dir = storage_path('app/public/admin/data-tpu');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Buat sample placeholder images berkualitas untuk tiap TPU jika belum ada
        $sampleImages = [
            'tpu_lambara_doc1.jpg',
            'tpu_lambara_doc2.jpg',
            'tpu_lambara_doc3.jpg',
            'tpu_poboya_doc1.jpg',
            'tpu_poboya_doc2.jpg',
            'tpu_poboya_doc3.jpg',
            'tpu_valagguni_doc1.jpg',
            'tpu_valagguni_doc2.jpg',
            'tpu_valagguni_doc3.jpg',
        ];

        foreach ($sampleImages as $img) {
            $path = $dir . '/' . $img;
            if (! File::exists($path)) {
                // Buat 1x1 / small image placeholder menggunakan GD jika tersedia
                if (function_exists('imagecreatetruecolor')) {
                    $im = imagecreatetruecolor(800, 500);
                    $bgColor = imagecolorallocate($im, 60, 110, 95);
                    $textColor = imagecolorallocate($im, 255, 255, 255);
                    imagefill($im, 0, 0, $bgColor);
                    imagestring($im, 5, 260, 240, 'Dokumentasi ' . str_replace(['tpu_', '.jpg', '_'], ['', '', ' '], $img), $textColor);
                    imagejpeg($im, $path, 85);
                    imagedestroy($im);
                } else {
                    File::put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));
                }
            }
        }

        // 1. TPU Lambara (Data persis dari spreadsheet user)
        DataTpu::updateOrCreate(
            ['nama_tpu' => 'TPU Lambara'],
            [
                'luas_area_makam' => '2 Ha',
                'vegetasi' => [
                    ['jenis_pohon' => 'Trambesi', 'jumlah' => '13'],
                    ['jenis_pohon' => 'Tanjung', 'jumlah' => '8'],
                    ['jenis_pohon' => 'Pinang Hias', 'jumlah' => '1'],
                    ['jenis_pohon' => 'Bunga Salak', 'jumlah' => '1'],
                    ['jenis_pohon' => 'Pohon Bambu', 'jumlah' => '1 rumpun'],
                    ['jenis_pohon' => 'Kamboja', 'jumlah' => '61'],
                    ['jenis_pohon' => 'Bonsai Hutan / Serut', 'jumlah' => '1'],
                ],
                'kapasitas_blok' => [
                    ['agama' => 'Islam', 'jumlah_blok' => '88 blok', 'jumlah_makam' => '1.408 makam'],
                    ['agama' => 'Kristen', 'jumlah_blok' => '26 blok', 'jumlah_makam' => '416 makam'],
                    ['agama' => 'Hindu', 'jumlah_blok' => '12 blok', 'jumlah_makam' => '192 makam'],
                    ['agama' => 'Buddha', 'jumlah_blok' => '11 blok', 'jumlah_makam' => '176 makam'],
                ],
                'foto_dokumentasi' => [
                    'admin/data-tpu/tpu_lambara_doc1.jpg',
                    'admin/data-tpu/tpu_lambara_doc2.jpg',
                    'admin/data-tpu/tpu_lambara_doc3.jpg',
                ],
            ]
        );

        // 2. TPU Poboya
        DataTpu::updateOrCreate(
            ['nama_tpu' => 'TPU Poboya'],
            [
                'luas_area_makam' => '5 Ha',
                'vegetasi' => [
                    ['jenis_pohon' => 'Trembesi', 'jumlah' => '25'],
                    ['jenis_pohon' => 'Kamboja', 'jumlah' => '80'],
                    ['jenis_pohon' => 'Mahoni', 'jumlah' => '15'],
                    ['jenis_pohon' => 'Pucuk Merah', 'jumlah' => '30'],
                    ['jenis_pohon' => 'Bougenville', 'jumlah' => '12'],
                ],
                'kapasitas_blok' => [
                    ['agama' => 'Islam', 'jumlah_blok' => '120 blok', 'jumlah_makam' => '2.100 makam'],
                    ['agama' => 'Kristen', 'jumlah_blok' => '40 blok', 'jumlah_makam' => '680 makam'],
                    ['agama' => 'Hindu', 'jumlah_blok' => '15 blok', 'jumlah_makam' => '240 makam'],
                    ['agama' => 'Buddha', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '150 makam'],
                ],
                'foto_dokumentasi' => [
                    'admin/data-tpu/tpu_poboya_doc1.jpg',
                    'admin/data-tpu/tpu_poboya_doc2.jpg',
                ],
            ]
        );

        // 3. TPU Valagguni
        DataTpu::updateOrCreate(
            ['nama_tpu' => 'TPU Valagguni'],
            [
                'luas_area_makam' => '3.5 Ha',
                'vegetasi' => [
                    ['jenis_pohon' => 'Kamboja', 'jumlah' => '55'],
                    ['jenis_pohon' => 'Trembesi', 'jumlah' => '18'],
                    ['jenis_pohon' => 'Ketapang Kencana', 'jumlah' => '12'],
                    ['jenis_pohon' => 'Tabebuya', 'jumlah' => '20'],
                    ['jenis_pohon' => 'Palem Raja', 'jumlah' => '10'],
                ],
                'kapasitas_blok' => [
                    ['agama' => 'Islam', 'jumlah_blok' => '95 blok', 'jumlah_makam' => '1.650 makam'],
                    ['agama' => 'Kristen', 'jumlah_blok' => '32 blok', 'jumlah_makam' => '520 makam'],
                    ['agama' => 'Hindu', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '160 makam'],
                    ['agama' => 'Buddha', 'jumlah_blok' => '8 blok', 'jumlah_makam' => '120 makam'],
                ],
                'foto_dokumentasi' => [
                    'admin/data-tpu/tpu_valagguni_doc1.jpg',
                ],
            ]
        );
    }
}
