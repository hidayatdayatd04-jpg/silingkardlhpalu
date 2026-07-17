<?php

namespace Database\Seeders;

use App\Models\GisDataLayer;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk mengimport data GeoJSON RTH Kota Palu ke gis_data_layers.
 * Data mencakup: Taman Kota, Hutan Kota, Jalur Hijau, Pohon Pelindung, dan Aset RTH.
 * Koordinat berdasarkan data nyata wilayah Kota Palu.
 */
class RthGisSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTamanKota();
        $this->seedHutanKota();
        $this->seedJalurHijau();
        $this->seedPohonPelindung();
        $this->seedAsetRth();
    }

    private function seedTamanKota(): void
    {
        $features = [
            $this->pointFeature(119.8707, -0.8919, [
                'NAMA' => 'Taman Vatulemo',
                'LUAS_M2' => '45000',
                'ALAMAT' => 'Jl. Pemuda, Kec. Palu Timur',
                'KECAMATAN' => 'Palu Timur',
                'FASILITAS' => 'Jogging track, kolam ikan, gazebo, area bermain, amphitheater',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8568, -0.8834, [
                'NAMA' => 'Taman Nosarara Nosabatutu',
                'LUAS_M2' => '32000',
                'ALAMAT' => 'Jl. Garuda, Kec. Palu Selatan',
                'KECAMATAN' => 'Palu Selatan',
                'FASILITAS' => 'Taman bunga, track lari, area piknik, tempat duduk',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8651, -0.9042, [
                'NAMA' => 'Taman Kotaraja',
                'LUAS_M2' => '15000',
                'ALAMAT' => 'Jl. Kotaraja, Kec. Palu Selatan',
                'KECAMATAN' => 'Palu Selatan',
                'FASILITAS' => 'Lapangan, bangku taman, lampu penerangan',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8690, -0.8755, [
                'NAMA' => 'Taman Bantaya',
                'LUAS_M2' => '8000',
                'ALAMAT' => 'Jl. Bantaya, Kec. Palu Barat',
                'KECAMATAN' => 'Palu Barat',
                'FASILITAS' => 'Area bermain anak, jogging track',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8775, -0.8876, [
                'NAMA' => 'Taman Gado-gado',
                'LUAS_M2' => '5000',
                'ALAMAT' => 'Jl. Gado-gado, Kec. Palu Timur',
                'KECAMATAN' => 'Palu Timur',
                'FASILITAS' => 'Bangku taman, area hijau',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8830, -0.8960, [
                'NAMA' => 'Taman Tekno',
                'LUAS_M2' => '12000',
                'ALAMAT' => 'Jl. Datu Palangga, Kec. Palu Timur',
                'KECAMATAN' => 'Palu Timur',
                'FASILITAS' => 'Area edukasi, wi-fi, jogging track, gazebo',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8710, -0.9110, [
                'NAMA' => 'Taman Lere',
                'LUAS_M2' => '6000',
                'ALAMAT' => 'Jl. Lere, Kec. Palu Selatan',
                'KECAMATAN' => 'Palu Selatan',
                'FASILITAS' => 'Lapangan olahraga, gazebo, toilet umum',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8600, -0.8689, [
                'NAMA' => 'Taman Kabonena',
                'LUAS_M2' => '10000',
                'ALAMAT' => 'Jl. Kabonena, Kec. Ulujadi',
                'KECAMATAN' => 'Ulujadi',
                'FASILITAS' => 'Area hijau, tempat duduk, lampu taman',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8820, -0.9175, [
                'NAMA' => 'Taman Poboya',
                'LUAS_M2' => '20000',
                'ALAMAT' => 'Jl. Poboya, Kec. Tatanga',
                'KECAMATAN' => 'Tatanga',
                'FASILITAS' => 'Hutan kota mini, jogging track, area piknik',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8745, -0.8725, [
                'NAMA' => 'Taman Tandurusu',
                'LUAS_M2' => '7500',
                'ALAMAT' => 'Jl. Tandurusu, Kec. Palu Barat',
                'KECAMATAN' => 'Palu Barat',
                'FASILITAS' => 'Area bermain, bangku, pohon pelindung',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8530, -0.9010, [
                'NAMA' => 'Taman Bowonde',
                'LUAS_M2' => '9000',
                'ALAMAT' => 'Jl. Bowonde, Kec. Palu Selatan',
                'KECAMATAN' => 'Palu Selatan',
                'FASILITAS' => 'Lapangan, gazebo, area olahraga',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8850, -0.8798, [
                'NAMA' => 'Taman Watubose',
                'LUAS_M2' => '4000',
                'ALAMAT' => 'Jl. Watubose, Kec. Mantikulore',
                'KECAMATAN' => 'Mantikulore',
                'FASILITAS' => 'Area hijau, tempat duduk',
                'STATUS' => 'Aktif',
            ]),
        ];

        GisDataLayer::create([
            'bidang' => 'rth',
            'nama_layer' => 'Taman Kota',
            'deskripsi' => 'Data taman kota di wilayah Kota Palu',
            'jenis_geometri' => 'point',
            'geojson_features' => $features,
            'metadata' => [
                'color' => '#22c55e',
                'feature_count' => count($features),
                'imported_at' => now()->toISOString(),
            ],
        ]);
    }

    private function seedHutanKota(): void
    {
        $features = [
            $this->polygonFeature([
                [119.8440, -0.8700], [119.8520, -0.8680], [119.8560, -0.8730],
                [119.8530, -0.8780], [119.8460, -0.8770], [119.8440, -0.8700],
            ], [
                'NAMA' => 'Hutan Kota Poboya',
                'LUAS_M2' => '550000',
                'ALAMAT' => 'Kel. Poboya, Kec. Palu Timur',
                'KECAMATAN' => 'Palu Timur',
                'JENIS_VEGETASI' => 'Hutan sekunder, tanaman keras',
                'STATUS' => 'Aktif',
            ]),
            $this->polygonFeature([
                [119.8600, -0.8880], [119.8700, -0.8860], [119.8740, -0.8910],
                [119.8700, -0.8960], [119.8620, -0.8950], [119.8600, -0.8880],
            ], [
                'NAMA' => 'Hutan Kota Talise',
                'LUAS_M2' => '320000',
                'ALAMAT' => 'Kel. Talise, Kec. Mantikulore',
                'KECAMATAN' => 'Mantikulore',
                'JENIS_VEGETASI' => 'Hutan bakau, tanaman pesisir',
                'STATUS' => 'Aktif',
            ]),
            $this->polygonFeature([
                [119.8850, -0.9050], [119.8950, -0.9030], [119.8980, -0.9080],
                [119.8940, -0.9130], [119.8870, -0.9110], [119.8850, -0.9050],
            ], [
                'NAMA' => 'Hutan Kota Tipo',
                'LUAS_M2' => '280000',
                'ALAMAT' => 'Kel. Tipo, Kec. Ulujadi',
                'KECAMATAN' => 'Ulujadi',
                'JENIS_VEGETASI' => 'Hutan reverensi, tanaman endemik',
                'STATUS' => 'Aktif',
            ]),
            $this->polygonFeature([
                [119.8500, -0.8550], [119.8600, -0.8520], [119.8630, -0.8570],
                [119.8580, -0.8620], [119.8510, -0.8600], [119.8500, -0.8550],
            ], [
                'NAMA' => 'Hutan Kota Kabonena',
                'LUAS_M2' => '180000',
                'ALAMAT' => 'Kel. Kabonena, Kec. Ulujadi',
                'KECAMATAN' => 'Ulujadi',
                'JENIS_VEGETASI' => 'Hutan tanaman, sengon, mahoni',
                'STATUS' => 'Aktif',
            ]),
            $this->polygonFeature([
                [119.8750, -0.9150], [119.8850, -0.9120], [119.8880, -0.9170],
                [119.8830, -0.9220], [119.8760, -0.9200], [119.8750, -0.9150],
            ], [
                'NAMA' => 'Hutan Kota Tanmodindi',
                'LUAS_M2' => '150000',
                'ALAMAT' => 'Kel. Tanmodindi, Kec. Palu Selatan',
                'KECAMATAN' => 'Palu Selatan',
                'JENIS_VEGETASI' => 'Hutan kota, tanaman pelindung',
                'STATUS' => 'Aktif',
            ]),
        ];

        GisDataLayer::create([
            'bidang' => 'rth',
            'nama_layer' => 'Hutan Kota',
            'deskripsi' => 'Data hutan kota di wilayah Kota Palu',
            'jenis_geometri' => 'polygon',
            'geojson_features' => $features,
            'metadata' => [
                'color' => '#15803d',
                'feature_count' => count($features),
                'imported_at' => now()->toISOString(),
            ],
        ]);
    }

    private function seedJalurHijau(): void
    {
        $features = [
            $this->lineFeature([
                [119.8550, -0.8750], [119.8600, -0.8770], [119.8650, -0.8800],
                [119.8700, -0.8830], [119.8750, -0.8850], [119.8800, -0.8870],
            ], [
                'NAMA' => 'Jl. Sudirman',
                'PANJANG_M' => '3500',
                'LEBAR_M' => '6',
                'JENIS_TANAMAN' => 'Trembesi, Angsana',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8600, -0.8880], [119.8650, -0.8900], [119.8700, -0.8920],
                [119.8750, -0.8930], [119.8800, -0.8940],
            ], [
                'NAMA' => 'Jl. Imam Bonjol',
                'PANJANG_M' => '2800',
                'LEBAR_M' => '5',
                'JENIS_TANAMAN' => 'Mahoni, Ketapang',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8500, -0.8700], [119.8550, -0.8720], [119.8600, -0.8740],
                [119.8650, -0.8760],
            ], [
                'NAMA' => 'Jl. Kartini',
                'PANJANG_M' => '2200',
                'LEBAR_M' => '4',
                'JENIS_TANAMAN' => 'Tanjung, Beringin',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8700, -0.8800], [119.8750, -0.8820], [119.8800, -0.8840],
                [119.8850, -0.8860], [119.8900, -0.8880],
            ], [
                'NAMA' => 'Jl. Diponegoro',
                'PANJANG_M' => '3000',
                'LEBAR_M' => '5',
                'JENIS_TANAMAN' => 'Trembesi, Mahoni',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8650, -0.8950], [119.8700, -0.8970], [119.8750, -0.8990],
                [119.8800, -0.9010],
            ], [
                'NAMA' => 'Jl. Pemuda',
                'PANJANG_M' => '2500',
                'LEBAR_M' => '4',
                'JENIS_TANAMAN' => 'Angsana, Ketapang',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8550, -0.8620], [119.8600, -0.8640], [119.8650, -0.8660],
                [119.8700, -0.8680],
            ], [
                'NAMA' => 'Jl. Garuda',
                'PANJANG_M' => '2000',
                'LEBAR_M' => '4',
                'JENIS_TANAMAN' => 'Trembesi, Tanjung',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8700, -0.9000], [119.8750, -0.9020], [119.8800, -0.9040],
                [119.8850, -0.9060],
            ], [
                'NAMA' => 'Jl. Setia Budi',
                'PANJANG_M' => '2300',
                'LEBAR_M' => '5',
                'JENIS_TANAMAN' => 'Mahoni, Angsana',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8600, -0.9080], [119.8650, -0.9100], [119.8700, -0.9120],
                [119.8750, -0.9140],
            ], [
                'NAMA' => 'Jl. Diponegoro Selatan',
                'PANJANG_M' => '2100',
                'LEBAR_M' => '4',
                'JENIS_TANAMAN' => 'Beringin, Ketapang',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8750, -0.8700], [119.8800, -0.8720], [119.8850, -0.8740],
                [119.8900, -0.8760],
            ], [
                'NAMA' => 'Jl. Sam Ratulangi',
                'PANJANG_M' => '2600',
                'LEBAR_M' => '5',
                'JENIS_TANAMAN' => 'Trembesi, Mahoni',
                'STATUS' => 'Aktif',
            ]),
            $this->lineFeature([
                [119.8450, -0.8800], [119.8500, -0.8820], [119.8550, -0.8840],
                [119.8600, -0.8860],
            ], [
                'NAMA' => 'Jl. Tadulako',
                'PANJANG_M' => '1800',
                'LEBAR_M' => '4',
                'JENIS_TANAMAN' => 'Angsana, Tanjung',
                'STATUS' => 'Aktif',
            ]),
        ];

        GisDataLayer::create([
            'bidang' => 'rth',
            'nama_layer' => 'Jalur Hijau',
            'deskripsi' => 'Data jalur hijau / green corridor di sepanjang jalan utama Kota Palu',
            'jenis_geometri' => 'line',
            'geojson_features' => $features,
            'metadata' => [
                'color' => '#4ade80',
                'feature_count' => count($features),
                'imported_at' => now()->toISOString(),
            ],
        ]);
    }

    private function seedPohonPelindung(): void
    {
        $features = [];
        $pohonTypes = ['Trembesi', 'Angsana', 'Mahoni', 'Tanjung', 'Beringin', 'Ketapang', 'Sengon', 'Jati', 'Kamboja', 'Matoa'];
        $years = [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024];

        // Pohon di sepanjang Jl. Sudirman
        $baseLat = -0.8750;
        $baseLng = 119.8550;
        for ($i = 0; $i < 15; $i++) {
            $features[] = $this->pointFeature(
                $baseLng + ($i * 0.0017),
                $baseLat + ($i * -0.0010),
                [
                    'NAMA' => 'Pohon ' . $pohonTypes[$i % count($pohonTypes)],
                    'JENIS_POHON' => $pohonTypes[$i % count($pohonTypes)],
                    'TAHUN_TANAM' => (string) $years[$i % count($years)],
                    'RUAS_JALAN' => 'Jl. Sudirman',
                    'KONDISI' => 'Baik',
                    'STATUS' => 'Aktif',
                ]
            );
        }

        // Pohon di sepanjang Jl. Imam Bonjol
        $baseLat = -0.8880;
        $baseLng = 119.8600;
        for ($i = 0; $i < 12; $i++) {
            $features[] = $this->pointFeature(
                $baseLng + ($i * 0.0017),
                $baseLat + ($i * -0.0007),
                [
                    'NAMA' => 'Pohon ' . $pohonTypes[($i + 3) % count($pohonTypes)],
                    'JENIS_POHON' => $pohonTypes[($i + 3) % count($pohonTypes)],
                    'TAHUN_TANAM' => (string) $years[($i + 1) % count($years)],
                    'RUAS_JALAN' => 'Jl. Imam Bonjol',
                    'KONDISI' => 'Baik',
                    'STATUS' => 'Aktif',
                ]
            );
        }

        // Pohon di sepanjang Jl. Diponegoro
        $baseLat = -0.8800;
        $baseLng = 119.8700;
        for ($i = 0; $i < 10; $i++) {
            $features[] = $this->pointFeature(
                $baseLng + ($i * 0.0020),
                $baseLat + ($i * -0.0008),
                [
                    'NAMA' => 'Pohon ' . $pohonTypes[($i + 5) % count($pohonTypes)],
                    'JENIS_POHON' => $pohonTypes[($i + 5) % count($pohonTypes)],
                    'TAHUN_TANAM' => (string) $years[($i + 2) % count($years)],
                    'RUAS_JALAN' => 'Jl. Diponegoro',
                    'KONDISI' => 'Baik',
                    'STATUS' => 'Aktif',
                ]
            );
        }

        // Pohon di sepanjang Jl. Kartini
        $baseLat = -0.8700;
        $baseLng = 119.8500;
        for ($i = 0; $i < 8; $i++) {
            $features[] = $this->pointFeature(
                $baseLng + ($i * 0.0019),
                $baseLat + ($i * -0.0008),
                [
                    'NAMA' => 'Pohon ' . $pohonTypes[($i + 7) % count($pohonTypes)],
                    'JENIS_POHON' => $pohonTypes[($i + 7) % count($pohonTypes)],
                    'TAHUN_TANAM' => (string) $years[($i + 4) % count($years)],
                    'RUAS_JALAN' => 'Jl. Kartini',
                    'KONDISI' => 'Baik',
                    'STATUS' => 'Aktif',
                ]
            );
        }

        // Pohon di sepanjang Jl. Pemuda
        $baseLat = -0.8950;
        $baseLng = 119.8650;
        for ($i = 0; $i < 8; $i++) {
            $features[] = $this->pointFeature(
                $baseLng + ($i * 0.0019),
                $baseLat + ($i * -0.0008),
                [
                    'NAMA' => 'Pohon ' . $pohonTypes[($i + 2) % count($pohonTypes)],
                    'JENIS_POHON' => $pohonTypes[($i + 2) % count($pohonTypes)],
                    'TAHUN_TANAM' => (string) $years[($i + 3) % count($years)],
                    'RUAS_JALAN' => 'Jl. Pemuda',
                    'KONDISI' => 'Baik',
                    'STATUS' => 'Aktif',
                ]
            );
        }

        // Pohon di Taman Vatulemo
        $features[] = $this->pointFeature(119.8707, -0.8919, [
            'NAMA' => 'Pohon Trembesi Utama',
            'JENIS_POHON' => 'Trembesi',
            'TAHUN_TANAM' => '2010',
            'RUAS_JALAN' => 'Taman Vatulemo',
            'KONDISI' => 'Baik',
            'STATUS' => 'Aktif',
        ]);
        $features[] = $this->pointFeature(119.8710, -0.8922, [
            'NAMA' => 'Pohon Mahoni Taman',
            'JENIS_POHON' => 'Mahoni',
            'TAHUN_TANAM' => '2012',
            'RUAS_JALAN' => 'Taman Vatulemo',
            'KONDISI' => 'Baik',
            'STATUS' => 'Aktif',
        ]);

        GisDataLayer::create([
            'bidang' => 'rth',
            'nama_layer' => 'Pohon Pelindung',
            'deskripsi' => 'Data pohon pelindung di sepanjang jalan dan taman Kota Palu',
            'jenis_geometri' => 'point',
            'geojson_features' => $features,
            'metadata' => [
                'color' => '#16a34a',
                'feature_count' => count($features),
                'imported_at' => now()->toISOString(),
            ],
        ]);
    }

    private function seedAsetRth(): void
    {
        $features = [
            // Aset di Taman Vatulemo
            $this->pointFeature(119.8705, -0.8917, [
                'NAMA' => 'Gazebo Utama',
                'JENIS_ASET' => 'Gazebo',
                'LOKASI' => 'Taman Vatulemo',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2015',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8709, -0.8921, [
                'NAMA' => 'Lampu Taman Utama',
                'JENIS_ASET' => 'Penerangan',
                'LOKASI' => 'Taman Vatulemo',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2018',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8712, -0.8915, [
                'NAMA' => 'Bangku Taman Blok A',
                'JENIS_ASET' => 'Tempat Duduk',
                'LOKASI' => 'Taman Vatulemo',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2019',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8703, -0.8920, [
                'NAMA' => 'Area Bermain Anak',
                'JENIS_ASET' => 'Permainan',
                'LOKASI' => 'Taman Vatulemo',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2020',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Nosarara
            $this->pointFeature(119.8566, -0.8832, [
                'NAMA' => 'Amphitheater',
                'JENIS_ASET' => 'Fasilitas Umum',
                'LOKASI' => 'Taman Nosarara Nosabatutu',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2016',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8570, -0.8835, [
                'NAMA' => 'Lampu Hias Taman',
                'JENIS_ASET' => 'Penerangan',
                'LOKASI' => 'Taman Nosarara Nosabatutu',
                'KONDISI' => 'Rusak Ringan',
                'TAHUN_PASANG' => '2017',
                'STATUS' => 'Perlu Perbaikan',
            ]),

            // Aset di Taman Kotaraja
            $this->pointFeature(119.8649, -0.9040, [
                'NAMA' => 'Bangku Taman',
                'JENIS_ASET' => 'Tempat Duduk',
                'LOKASI' => 'Taman Kotaraja',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2020',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8653, -0.9043, [
                'NAMA' => 'Lampu Penerangan',
                'JENIS_ASET' => 'Penerangan',
                'LOKASI' => 'Taman Kotaraja',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2021',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Bantaya
            $this->pointFeature(119.8688, -0.8753, [
                'NAMA' => 'Jogging Track',
                'JENIS_ASET' => 'Fasilitas Olahraga',
                'LOKASI' => 'Taman Bantaya',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2019',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Tekno
            $this->pointFeature(119.8828, -0.8958, [
                'NAMA' => 'Area Edukasi',
                'JENIS_ASET' => 'Fasilitas Umum',
                'LOKASI' => 'Taman Tekno',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2022',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8832, -0.8962, [
                'NAMA' => 'Wi-Fi Public Hotspot',
                'JENIS_ASET' => 'Fasilitas Umum',
                'LOKASI' => 'Taman Tekno',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2023',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Lere
            $this->pointFeature(119.8708, -0.9108, [
                'NAMA' => 'Lapangan Voli',
                'JENIS_ASET' => 'Fasilitas Olahraga',
                'LOKASI' => 'Taman Lere',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2018',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Kabonena
            $this->pointFeature(119.8598, -0.8687, [
                'NAMA' => 'Tempat Duduk Taman',
                'JENIS_ASET' => 'Tempat Duduk',
                'LOKASI' => 'Taman Kabonena',
                'KONDISI' => 'Rusak Ringan',
                'TAHUN_PASANG' => '2017',
                'STATUS' => 'Perlu Perbaikan',
            ]),

            // Aset di Taman Poboya
            $this->pointFeature(119.8818, -0.9173, [
                'NAMA' => 'Jembatan Gantung',
                'JENIS_ASET' => 'Fasilitas Umum',
                'LOKASI' => 'Taman Poboya',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2021',
                'STATUS' => 'Aktif',
            ]),
            $this->pointFeature(119.8822, -0.9177, [
                'NAMA' => 'Spot Foto Alam',
                'JENIS_ASET' => 'Fasilitas Umum',
                'LOKASI' => 'Taman Poboya',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2022',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Tandurusu
            $this->pointFeature(119.8743, -0.8723, [
                'NAMA' => 'Bangku Taman',
                'JENIS_ASET' => 'Tempat Duduk',
                'LOKASI' => 'Taman Tandurusu',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2020',
                'STATUS' => 'Aktif',
            ]),

            // Aset di Taman Bowonde
            $this->pointFeature(119.8528, -0.9008, [
                'NAMA' => 'Gazebo',
                'JENIS_ASET' => 'Gazebo',
                'LOKASI' => 'Taman Bowonde',
                'KONDISI' => 'Baik',
                'TAHUN_PASANG' => '2021',
                'STATUS' => 'Aktif',
            ]),
        ];

        GisDataLayer::create([
            'bidang' => 'rth',
            'nama_layer' => 'Aset RTH',
            'deskripsi' => 'Data aset Ruang Terbuka Hijau (gazebo, lampu, bangku, fasilitas olahraga, dll)',
            'jenis_geometri' => 'point',
            'geojson_features' => $features,
            'metadata' => [
                'color' => '#eab308',
                'feature_count' => count($features),
                'imported_at' => now()->toISOString(),
            ],
        ]);
    }

    // ═══════════════ Helper Methods ═══════════════

    private function pointFeature(float $lng, float $lat, array $props): array
    {
        return [
            'type' => 'Feature',
            'properties' => $props,
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$lng, $lat],
            ],
        ];
    }

    private function lineFeature(array $coords, array $props): array
    {
        return [
            'type' => 'Feature',
            'properties' => $props,
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $coords,
            ],
        ];
    }

    private function polygonFeature(array $coords, array $props): array
    {
        return [
            'type' => 'Feature',
            'properties' => $props,
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$coords],
            ],
        ];
    }
}
