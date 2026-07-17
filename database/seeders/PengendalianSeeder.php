<?php

namespace Database\Seeders;

use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\LaporanKategori;
use App\Enums\LaporanStatus;
use App\Enums\PengaduanStatus;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PengendalianSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLaporanPengendalian();
    }

    private function seedLaporanPengendalian(): void
    {
        $laporans = [
            [
                'nomor_tiket' => 'PGD-' . date('ymd') . '-001',
                'bidang' => Bidang::PENGENDALIAN->value,
                'nama_pelapor' => 'Andi Pratama',
                'nomor_hp' => '082156789012',
                'email' => 'andi.pratama@example.com',
                'kategori' => 'Pengaduan Pengendalian',
                'jenis_pengaduan' => JenisPengaduanPengendalian::PEMBAKARAN_SAMPAH->value,
                'deskripsi' => 'Pembakaran sampah secara terus menerus di lahan kosong dekat perumahan warga',
                'alamat' => 'Jl. Veteran No. 12, Kelurahan Lere, Palu',
                'latitude' => -0.9100,
                'longitude' => 119.8800,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Sudah dilakukan tindak lanjut',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => $this->copyDummyImage('selesai1.jpg', 'pengendalian'),
            ],
            [
                'nomor_tiket' => 'PGD-' . date('ymd') . '-002',
                'bidang' => Bidang::PENGENDALIAN->value,
                'nama_pelapor' => 'Sri Wahyuni',
                'nomor_hp' => '082156789013',
                'email' => 'sri.wahyuni@example.com',
                'kategori' => 'Pengaduan Pengendalian',
                'jenis_pengaduan' => JenisPengaduanPengendalian::LIMBAH_B3->value,
                'deskripsi' => 'Ditemukan pembuangan limbah medis di area terbuka',
                'alamat' => 'Jl. Imam Bonjol No. 78, Palu',
                'latitude' => -0.9012,
                'longitude' => 119.8650,
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_admin' => null,
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => null,
            ],
            [
                'nomor_tiket' => 'PGD-' . date('ymd') . '-003',
                'bidang' => Bidang::PENGENDALIAN->value,
                'nama_pelapor' => 'Muhammad Rizki',
                'nomor_hp' => '082156789014',
                'email' => null,
                'kategori' => 'Laporan Pengendalian',
                'jenis_pengaduan' => JenisPengaduanPengendalian::BANJIR->value,
                'deskripsi' => 'Banjir akibat saluran drainase tersumbat sampah',
                'alamat' => 'Jl. Diponegoro, Kelurahan Besusu, Palu',
                'latitude' => -0.8945,
                'longitude' => 119.8745,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Koordinasi dengan Dinas PU untuk pembersihan drainase',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => $this->copyDummyImage('selesai2.jpg', 'pengendalian'),
            ],
            [
                'nomor_tiket' => 'PGD-' . date('ymd') . '-004',
                'bidang' => Bidang::PENGENDALIAN->value,
                'nama_pelapor' => 'Fatimah Zahra',
                'nomor_hp' => '082156789015',
                'email' => 'fatimah.z@example.com',
                'kategori' => 'Pengaduan Pengendalian',
                'jenis_pengaduan' => JenisPengaduanPengendalian::LONGSOR->value,
                'deskripsi' => 'Tanah longsor di tebing akibat penggundulan hutan',
                'alamat' => 'Kelurahan Pantoloan, Palu',
                'latitude' => -0.8650,
                'longitude' => 119.8450,
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_admin' => null,
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => null,
            ],
        ];

        foreach ($laporans as $laporan) {
            $created = Laporan::create($laporan);
            
            // Tambahkan foto bukti
            for ($i = 1; $i <= 2; $i++) {
                LaporanFoto::create([
                    'laporan_id' => $created->id,
                    'path_foto' => $this->copyDummyImage("laporan_pengendalian{$i}.jpg", 'pengendalian'),
                ]);
            }
        }
    }

    private function copyDummyImage(string $filename, string $folder): string
    {
        $sourcePath = storage_path("app/public/seeder-images/{$filename}");
        
        if (!File::exists($sourcePath)) {
            return 'placeholder.jpg';
        }

        $destinationPath = "admin/{$folder}/{$filename}";
        Storage::disk('public')->copy("seeder-images/{$filename}", $destinationPath);
        
        return $destinationPath;
    }
}
