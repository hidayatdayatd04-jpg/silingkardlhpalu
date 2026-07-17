<?php

namespace Database\Seeders;

use App\Enums\Bidang;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\LaporanKategori;
use App\Enums\PengaduanStatus;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LaporanSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLaporanRth();
        $this->seedLaporanSampah();
    }

    private function seedLaporanRth(): void
    {
        $laporans = [
            [
                'nomor_tiket' => 'RTH-' . date('ymd') . '-001',
                'bidang' => Bidang::RTH->value,
                'nama_pelapor' => 'Bambang Sutrisno',
                'nomor_hp' => '082167890234',
                'email' => 'bambang.s@example.com',
                'kategori' => JenisPengaduanRth::PENEBANGAN_POHON_LIAR->value,
                'jenis_pengaduan' => JenisPengaduanRth::PENEBANGAN_POHON_LIAR->value,
                'deskripsi' => 'Penebangan pohon besar tanpa izin di kawasan jalur hijau',
                'alamat' => 'Jl. Jend. Sudirman KM 3, Palu',
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Telah dilakukan penindakan',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => $this->copyDummyImage('rth_selesai1.jpg', 'rth'),
            ],
            [
                'nomor_tiket' => 'RTH-' . date('ymd') . '-002',
                'bidang' => Bidang::RTH->value,
                'nama_pelapor' => 'Dewi Lestari',
                'nomor_hp' => '082167890235',
                'email' => 'dewi.lestari@example.com',
                'kategori' => JenisPengaduanRth::TAMAN_RUSAK_VANDALISME->value,
                'jenis_pengaduan' => JenisPengaduanRth::TAMAN_RUSAK_VANDALISME->value,
                'deskripsi' => 'Fasilitas taman dirusak, bangku taman dicoret-coret',
                'alamat' => 'Taman Vatulemo, Palu',
                'latitude' => -0.9045,
                'longitude' => 119.8765,
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_admin' => null,
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => null,
            ],
            [
                'nomor_tiket' => 'RTH-' . date('ymd') . '-003',
                'bidang' => Bidang::RTH->value,
                'nama_pelapor' => 'Eko Prasetyo',
                'nomor_hp' => '082167890236',
                'email' => null,
                'kategori' => JenisPengaduanRth::FASILITAS_TAMAN_MATI_LAMPU_RUSAK->value,
                'jenis_pengaduan' => JenisPengaduanRth::FASILITAS_TAMAN_MATI_LAMPU_RUSAK->value,
                'deskripsi' => 'Lampu taman mati total, gelap di malam hari',
                'alamat' => 'Taman Pusat Kota Palu',
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Koordinasi dengan tim listrik untuk perbaikan',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => null,
            ],
            [
                'nomor_tiket' => 'RTH-' . date('ymd') . '-004',
                'bidang' => Bidang::RTH->value,
                'nama_pelapor' => 'Fitri Handayani',
                'nomor_hp' => '082167890237',
                'email' => 'fitri.h@example.com',
                'kategori' => JenisPengaduanRth::LAHAN_RTH_BERALIH_FUNGSI->value,
                'jenis_pengaduan' => JenisPengaduanRth::LAHAN_RTH_BERALIH_FUNGSI->value,
                'deskripsi' => 'Lahan RTH dijadikan lahan parkir tanpa izin',
                'alamat' => 'Kelurahan Birobuli, Palu',
                'latitude' => -0.8823,
                'longitude' => 119.8550,
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
                    'path_foto' => $this->copyDummyImage("laporan_rth{$i}.jpg", 'rth'),
                ]);
            }
        }
    }

    private function seedLaporanSampah(): void
    {
        $laporans = [
            [
                'nomor_tiket' => 'SMP-' . date('ymd') . '-001',
                'bidang' => Bidang::SAMPAH_LB3->value,
                'nama_pelapor' => 'Hadi Wijaya',
                'nomor_hp' => '082178901345',
                'email' => 'hadi.w@example.com',
                'kategori' => 'Pengaduan Sampah',
                'jenis_pengaduan' => JenisPengaduanSampah::SAMPAH_MENUMPUK->value,
                'deskripsi' => 'Sampah menumpuk di TPS selama 3 hari tidak diangkut',
                'alamat' => 'TPS Jl. Sudirman, Palu',
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Sudah dikoordinasikan dengan tim pengangkutan',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => $this->copyDummyImage('sampah_selesai1.jpg', 'sampah'),
            ],
            [
                'nomor_tiket' => 'SMP-' . date('ymd') . '-002',
                'bidang' => Bidang::SAMPAH_LB3->value,
                'nama_pelapor' => 'Indah Permata',
                'nomor_hp' => '082178901346',
                'email' => 'indah.p@example.com',
                'kategori' => 'Pengaduan Sampah',
                'jenis_pengaduan' => JenisPengaduanSampah::ARMADA_TIDAK_LEWAT->value,
                'deskripsi' => 'Armada pengangkut sampah tidak melewati wilayah kami selama seminggu',
                'alamat' => 'Kelurahan Lere, Palu',
                'latitude' => -0.9100,
                'longitude' => 119.8800,
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_admin' => null,
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => null,
            ],
            [
                'nomor_tiket' => 'SMP-' . date('ymd') . '-003',
                'bidang' => Bidang::SAMPAH_LB3->value,
                'nama_pelapor' => 'Joko Susilo',
                'nomor_hp' => '082178901347',
                'email' => null,
                'kategori' => 'Pengaduan Sampah',
                'jenis_pengaduan' => JenisPengaduanSampah::SAMPAH_TIDAK_DIANGKUT->value,
                'deskripsi' => 'Sampah sudah dikeluarkan tapi tidak diangkut oleh petugas',
                'alamat' => 'Jl. Imam Bonjol, Palu',
                'latitude' => -0.9012,
                'longitude' => 119.8650,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Sudah ditindaklanjuti',
                'alasan_penolakan' => null,
                'bukti_foto_selesai' => $this->copyDummyImage('sampah_selesai2.jpg', 'sampah'),
            ],
        ];

        foreach ($laporans as $laporan) {
            $created = Laporan::create($laporan);
            
            // Tambahkan foto bukti
            for ($i = 1; $i <= 2; $i++) {
                LaporanFoto::create([
                    'laporan_id' => $created->id,
                    'path_foto' => $this->copyDummyImage("laporan_sampah{$i}.jpg", 'sampah'),
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
