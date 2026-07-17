<?php

namespace Database\Seeders;

use App\Enums\KeputusanTebangPohon;
use App\Enums\PengaduanStatus;
use App\Enums\PermohonanStatus;
use App\Models\DataTanamPohon;
use App\Models\JenisUsaha;
use App\Models\PerizinanTebangPohon;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\TamanKota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AplikasiRthSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPerizinanTebangPohon();
        $this->seedPermohonanPinjamTaman();
        $this->seedPermohonanRekomendasi();
    }

    private function seedPerizinanTebangPohon(): void
    {
        $perizinans = [
            [
                // nomor_tiket akan di-generate otomatis oleh model
                'nama_pemohon' => 'Ahmad Hidayat',
                'nomor_hp' => '082167890123',
                'email' => 'ahmad.hidayat@example.com',
                'surat_permohonan' => $this->copyDummyDoc('perizinan_surat1.pdf', 'perizinan-tebang-pohon'),
                'ktp_nib' => $this->copyDummyDoc('perizinan_ktp1.pdf', 'perizinan-tebang-pohon'),
                'alasan_penebangan' => 'Pohon sudah tua dan berisiko tumbang, mengancam keselamatan warga',
                'foto_pohon' => $this->copyDummyImage('pohon1.jpg', 'perizinan-tebang-pohon'),
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'rencana_ganti_tanam' => 'Akan menanam 3 pohon trembesi sebagai pengganti',
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_survei' => 'Sudah dilakukan survei lapangan. Pohon memang sudah tua dan berisiko',
                'keputusan' => KeputusanTebangPohon::DISETUJUI->value,
            ],
            [
                'nama_pemohon' => 'Siti Nurhaliza',
                'nomor_hp' => '082167890124',
                'email' => 'siti.nur@example.com',
                'surat_permohonan' => $this->copyDummyDoc('perizinan_surat2.pdf', 'perizinan-tebang-pohon'),
                'ktp_nib' => $this->copyDummyDoc('perizinan_ktp2.pdf', 'perizinan-tebang-pohon'),
                'alasan_penebangan' => 'Untuk pembangunan rumah',
                'foto_pohon' => $this->copyDummyImage('pohon2.jpg', 'perizinan-tebang-pohon'),
                'latitude' => -0.9012,
                'longitude' => 119.8650,
                'rencana_ganti_tanam' => 'Akan menanam 2 pohon mahoni',
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_survei' => 'Pohon masih dalam kondisi baik',
                'keputusan' => KeputusanTebangPohon::DITOLAK->value,
            ],
            [
                'nama_pemohon' => 'Budi Prasetyo',
                'nomor_hp' => '082167890125',
                'email' => null,
                'surat_permohonan' => $this->copyDummyDoc('perizinan_surat3.pdf', 'perizinan-tebang-pohon'),
                'ktp_nib' => $this->copyDummyDoc('perizinan_ktp3.pdf', 'perizinan-tebang-pohon'),
                'alasan_penebangan' => 'Pohon menghalangi akses jalan',
                'foto_pohon' => $this->copyDummyImage('pohon3.jpg', 'perizinan-tebang-pohon'),
                'latitude' => -0.8878,
                'longitude' => 119.8600,
                'rencana_ganti_tanam' => 'Akan menanam 2 pohon ketapang',
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_survei' => null,
                'keputusan' => null,
            ],
        ];

        $ticketCounter = 1;
        foreach ($perizinans as $data) {
            $data['nomor_tiket'] = 'PTB-' . date('Ymd') . '-' . str_pad($ticketCounter++, 4, '0', STR_PAD_LEFT);
            
            $perizinan = PerizinanTebangPohon::create($data);
            
            // Tambahkan data tanam pohon untuk yang disetujui
            if ($perizinan->keputusan === KeputusanTebangPohon::DISETUJUI->value) {
                DataTanamPohon::create([
                    'perizinan_tebang_pohon_id' => $perizinan->id,
                    'nama_penanggung_jawab' => $perizinan->nama_pemohon,
                    'jumlah_pohon' => 3,
                    'jenis_pohon' => 'Trembesi',
                    'latitude' => $perizinan->latitude,
                    'longitude' => $perizinan->longitude,
                    'foto_dokumentasi' => [
                        $this->copyDummyImage('tanam1.jpg', 'data-tanam-pohon'),
                        $this->copyDummyImage('tanam2.jpg', 'data-tanam-pohon'),
                    ],
                ]);
            }
        }
    }

    private function seedPermohonanPinjamTaman(): void
    {
        $tamans = TamanKota::all();

        $permohonans = [
            [
                'nomor_tiket' => 'PPT-' . date('Ymd') . '-0001',
                'nama_pemohon' => 'Yayasan Peduli Lingkungan',
                'nomor_hp' => '082178901234',
                'email' => 'ypl@example.com',
                'nama_kegiatan' => 'Peringatan Hari Bumi',
                'taman_kota_id' => $tamans[0]->id,
                'tanggal_kegiatan' => Carbon::now()->addDays(15)->setTime(8, 0),
                'tanggal_selesai' => Carbon::now()->addDays(15)->setTime(16, 0),
                'surat_permohonan' => $this->copyDummyDoc('pinjam_taman_surat1.pdf', 'pinjam-taman'),
                'jaminan_kebersihan' => true,
                'surat_jaminan' => $this->copyDummyDoc('pinjam_taman_jaminan1.pdf', 'pinjam-taman'),
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Permohonan disetujui. Harap menjaga kebersihan taman',
            ],
            [
                'nomor_tiket' => 'PPT-' . date('Ymd') . '-0002',
                'nama_pemohon' => 'Komunitas Seni Palu',
                'nomor_hp' => '082178901235',
                'email' => 'seni.palu@example.com',
                'nama_kegiatan' => 'Pentas Seni Budaya',
                'taman_kota_id' => $tamans[1]->id,
                'tanggal_kegiatan' => Carbon::now()->addDays(30)->setTime(10, 0),
                'tanggal_selesai' => Carbon::now()->addDays(30)->setTime(18, 0),
                'surat_permohonan' => $this->copyDummyDoc('pinjam_taman_surat2.pdf', 'pinjam-taman'),
                'jaminan_kebersihan' => true,
                'surat_jaminan' => $this->copyDummyDoc('pinjam_taman_jaminan2.pdf', 'pinjam-taman'),
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
                'catatan_admin' => null,
            ],
            [
                'nomor_tiket' => 'PPT-' . date('Ymd') . '-0003',
                'nama_pemohon' => 'Karang Taruna Besusu',
                'nomor_hp' => '082178901236',
                'email' => null,
                'nama_kegiatan' => 'Olahraga Bersama',
                'taman_kota_id' => $tamans[2]->id,
                'tanggal_kegiatan' => Carbon::now()->addDays(7)->setTime(6, 0),
                'tanggal_selesai' => Carbon::now()->addDays(7)->setTime(9, 0),
                'surat_permohonan' => $this->copyDummyDoc('pinjam_taman_surat3.pdf', 'pinjam-taman'),
                'jaminan_kebersihan' => false,
                'surat_jaminan' => null,
                'status' => PengaduanStatus::DITINJAU->value,
                'catatan_admin' => 'Harap melengkapi surat jaminan kebersihan',
            ],
        ];

        foreach ($permohonans as $permohonan) {
            PermohonanPinjamTaman::create($permohonan);
        }
    }

    private function seedPermohonanRekomendasi(): void
    {
        $jenisUsaha = JenisUsaha::all();

        $permohonans = [
            [
                'nomor_tiket' => 'PMH-' . date('Ymd') . '-0001',
                'nama_perusahaan' => 'PT Karya Mandiri Sejahtera',
                'nama_pemilik' => 'Hendra Kusuma',
                'npwp' => '12.345.678.9-123.000',
                'jenis_usaha' => 'Industri Makanan',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Industri Makanan')->first()->id,
                'alamat_lengkap' => 'Jl. Trans Sulawesi KM 8, Palu, Sulawesi Tengah',
                'nomor_telepon' => '0451-428888',
                'email' => 'karya.mandiri@example.com',
                'jenis_pengajuan' => 'Baru',
                'surat_permohonan' => $this->copyDummyDoc('rekomendasi_surat1.pdf', 'rekomendasi'),
                'status' => PermohonanStatus::DITINDAKLANJUTI->value,
                'catatan_verifikasi' => 'Dokumen lengkap dan telah diverifikasi',
                'dokumen_lengkap_terverifikasi' => true,
            ],
            [
                'nomor_tiket' => 'PMH-' . date('Ymd') . '-0002',
                'nama_perusahaan' => 'CV Sejahtera Abadi',
                'nama_pemilik' => 'Siti Aisyah',
                'npwp' => '12.345.678.9-123.001',
                'jenis_usaha' => 'Hotel',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Hotel')->first()->id,
                'alamat_lengkap' => 'Jl. Imam Bonjol No. 88, Palu, Sulawesi Tengah',
                'nomor_telepon' => '0451-429999',
                'email' => 'sejahtera.abadi@example.com',
                'jenis_pengajuan' => 'Perpanjangan',
                'surat_permohonan' => $this->copyDummyDoc('rekomendasi_surat2.pdf', 'rekomendasi'),
                'status' => PermohonanStatus::BELUM_DITINDAKLANJUTI->value,
                'catatan_verifikasi' => null,
                'dokumen_lengkap_terverifikasi' => false,
            ],
            [
                'nomor_tiket' => 'PMH-' . date('Ymd') . '-0003',
                'nama_perusahaan' => 'PT Maju Bersama',
                'nama_pemilik' => 'Rizky Ramadhan',
                'npwp' => '12.345.678.9-123.002',
                'jenis_usaha' => 'Pabrik',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Pabrik')->first()->id,
                'alamat_lengkap' => 'Jl. Industri No. 15, Palu, Sulawesi Tengah',
                'nomor_telepon' => '0451-427777',
                'email' => 'maju.bersama@example.com',
                'jenis_pengajuan' => 'Baru',
                'surat_permohonan' => $this->copyDummyDoc('rekomendasi_surat3.pdf', 'rekomendasi'),
                'status' => PermohonanStatus::DITINDAKLANJUTI->value,
                'catatan_verifikasi' => 'Perlu melengkapi dokumen AMDAL',
                'dokumen_lengkap_terverifikasi' => false,
            ],
        ];

        foreach ($permohonans as $permohonan) {
            $created = PermohonanRekomendasi::create($permohonan);
            
            // Tambahkan dokumen pendukung
            $this->seedDokumenForPermohonan($created);
        }
    }

    private function seedDokumenForPermohonan(PermohonanRekomendasi $permohonan): void
    {
        $dokumens = [
            [
                'nama_dokumen' => 'AMDAL',
                'path_dokumen' => $this->copyDummyDoc('permohonan_amdal.pdf', 'permohonan-rekomendasi'),
            ],
            [
                'nama_dokumen' => 'UKL-UPL',
                'path_dokumen' => $this->copyDummyDoc('permohonan_ukl_upl.pdf', 'permohonan-rekomendasi'),
            ],
            [
                'nama_dokumen' => 'SPPL',
                'path_dokumen' => $this->copyDummyDoc('permohonan_sppl.pdf', 'permohonan-rekomendasi'),
            ],
            [
                'nama_dokumen' => 'NIB',
                'path_dokumen' => $this->copyDummyDoc('permohonan_nib.pdf', 'permohonan-rekomendasi'),
            ],
        ];

        foreach ($dokumens as $dokumen) {
            PermohonanDokumen::create([
                'permohonan_rekomendasi_id' => $permohonan->id,
                ...$dokumen,
            ]);
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

    private function copyDummyDoc(string $filename, string $folder): string
    {
        $sourcePath = storage_path("app/public/seeder-documents/{$filename}");
        
        if (!File::exists($sourcePath)) {
            return 'placeholder.pdf';
        }

        $destinationPath = "admin/{$folder}/{$filename}";
        Storage::disk('public')->copy("seeder-documents/{$filename}", $destinationPath);
        
        return $destinationPath;
    }
}
