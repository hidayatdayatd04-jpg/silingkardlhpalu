<?php

namespace Database\Seeders;

use App\Enums\HasilSidak;
use App\Enums\JenisDokumenLingkungan;
use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\JenisSanksi;
use App\Enums\StatusDokumenLingkungan;
use App\Enums\StatusPengaduanTataPenataan;
use App\Enums\StatusSanksi;
use App\Enums\StatusTindakLanjutSidak;
use App\Models\JenisUsaha;
use App\Models\ObjekPengawasan;
use App\Models\ObjekPengawasanDokumen;
use App\Models\PengaduanTataPenataan;
use App\Models\PengaduanTataPenataanFoto;
use App\Models\Pelanggaran;
use App\Models\PelanggaranMedia;
use App\Models\Sanksi;
use App\Models\Sidak;
use App\Models\SidakMedia;
use App\Models\Sosialisasi;
use App\Models\SosialisasiFile;
use App\Models\SosialisasiPeserta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TataPenataanSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedObjekPengawasan();
        $this->seedPengaduanTataPenataan();
        $this->seedSidak();
        $this->seedPelanggaran();
        $this->seedSanksi();
        $this->seedSosialisasi();
    }

    private function seedObjekPengawasan(): void
    {
        $objeks = [
            [
                'nama_perusahaan' => 'PT Industri Makanan Sejahtera',
                'nama_penanggung_jawab' => 'Budi Santoso',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Industri Makanan')->first()->id,
                'alamat' => 'Jl. Trans Sulawesi KM 10, Palu',
                'latitude' => -0.8956,
                'longitude' => 119.8734,
                'no_hp' => '0451-426789',
                'email' => 'industri.sejahtera@example.com',
            ],
            [
                'nama_perusahaan' => 'CV Kimia Mandiri',
                'nama_penanggung_jawab' => 'Siti Rahma',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Industri Kimia')->first()->id,
                'alamat' => 'Jl. Trans Sulawesi KM 5, Palu',
                'latitude' => -0.8734,
                'longitude' => 119.8489,
                'no_hp' => '0451-424567',
                'email' => 'kimia.mandiri@example.com',
            ],
            [
                'nama_perusahaan' => 'Hotel Grand Palu',
                'nama_penanggung_jawab' => 'Ahmad Hidayat',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Hotel')->first()->id,
                'alamat' => 'Jl. Jend. Sudirman No. 100, Palu',
                'latitude' => -0.8989,
                'longitude' => 119.8707,
                'no_hp' => '0451-421111',
                'email' => 'hotel.grand@example.com',
            ],
            [
                'nama_perusahaan' => 'Restoran Mawar Merah',
                'nama_penanggung_jawab' => 'Rina Wijaya',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Restoran')->first()->id,
                'alamat' => 'Jl. Imam Bonjol No. 25, Palu',
                'latitude' => -0.9012,
                'longitude' => 119.8650,
                'no_hp' => '0451-422222',
                'email' => 'mawar.merah@example.com',
            ],
            [
                'nama_perusahaan' => 'Bengkel Jaya Motor',
                'nama_penanggung_jawab' => 'Hendra Kusuma',
                'jenis_usaha_id' => JenisUsaha::where('nama', 'Bengkel Motor')->first()->id,
                'alamat' => 'Jl. Imam Bonjol No. 45, Palu',
                'latitude' => -0.9015,
                'longitude' => 119.8655,
                'no_hp' => '0451-423456',
                'email' => 'bengkel.jaya@example.com',
            ],
        ];

        foreach ($objeks as $objek) {
            $created = ObjekPengawasan::create($objek);
            $this->seedDokumenForObjek($created);
        }
    }

    private function seedDokumenForObjek(ObjekPengawasan $objek): void
    {
        $dokumens = [
            [
                'jenis_dokumen' => JenisDokumenLingkungan::AMDAL->value,
                'status_dokumen' => StatusDokumenLingkungan::BERLAKU->value,
                'tanggal_berlaku' => Carbon::now()->subYear(),
                'tanggal_kadaluarsa' => Carbon::now()->addYears(2),
                'file_path' => $this->copyDummyDoc('amdal.pdf', 'objek-pengawasan'),
            ],
            [
                'jenis_dokumen' => JenisDokumenLingkungan::UKL_UPL->value,
                'status_dokumen' => StatusDokumenLingkungan::BERLAKU->value,
                'tanggal_berlaku' => Carbon::now()->subMonths(6),
                'tanggal_kadaluarsa' => Carbon::now()->addMonths(18),
                'file_path' => $this->copyDummyDoc('ukl_upl.pdf', 'objek-pengawasan'),
            ],
            [
                'jenis_dokumen' => JenisDokumenLingkungan::SPPL->value,
                'status_dokumen' => StatusDokumenLingkungan::KADALUARSA->value,
                'tanggal_berlaku' => Carbon::now()->subYears(2),
                'tanggal_kadaluarsa' => Carbon::now()->subMonths(3),
                'file_path' => $this->copyDummyDoc('sppl.pdf', 'objek-pengawasan'),
            ],
        ];

        foreach ($dokumens as $dokumen) {
            ObjekPengawasanDokumen::create([
                'objek_pengawasan_id' => $objek->id,
                ...$dokumen,
            ]);
        }
    }

    private function seedPengaduanTataPenataan(): void
    {
        $pengaduans = [
            [
                'nomor_tiket' => 'TTP-' . date('Ymd') . '-0001',
                'nama_pelapor' => 'Ahmad Zainuddin',
                'no_hp' => '082145678901',
                'email' => 'ahmad.zainuddin@example.com',
                'jenis_pengaduan' => JenisPengaduanTataPenataan::LIMBAH->value,
                'nama_terlapor' => 'CV Kimia Mandiri',
                'nama_perusahaan_terlapor' => 'CV Kimia Mandiri',
                'alamat' => 'Jl. Trans Sulawesi KM 5, Palu',
                'latitude' => -0.8734,
                'longitude' => 119.8489,
                'deskripsi' => 'Pembuangan limbah cair ke sungai tanpa pengolahan terlebih dahulu',
                'status' => StatusPengaduanTataPenataan::SELESAI->value,
                'catatan_admin' => 'Telah ditindaklanjuti dengan sidak',
                'assigned_user_id' => User::role('bidang-tata-penataan')->first()->id ?? null,
            ],
            [
                'nomor_tiket' => 'TTP-' . date('Ymd') . '-0002',
                'nama_pelapor' => 'Siti Nurhaliza',
                'no_hp' => '082145678902',
                'email' => 'siti.nur@example.com',
                'jenis_pengaduan' => JenisPengaduanTataPenataan::ASAP->value,
                'nama_terlapor' => 'Restoran Mawar Merah',
                'nama_perusahaan_terlapor' => 'Restoran Mawar Merah',
                'alamat' => 'Jl. Imam Bonjol No. 25, Palu',
                'latitude' => -0.9012,
                'longitude' => 119.8650,
                'deskripsi' => 'Asap dari dapur restoran mengganggu warga sekitar',
                'status' => StatusPengaduanTataPenataan::DITUGASKAN->value,
                'catatan_admin' => 'Sedang dalam pengecekan lapangan',
                'assigned_user_id' => User::role('bidang-tata-penataan')->first()->id ?? null,
            ],
            [
                'nomor_tiket' => 'TTP-' . date('Ymd') . '-0003',
                'nama_pelapor' => 'Budi Prasetyo',
                'no_hp' => '082145678903',
                'email' => null,
                'jenis_pengaduan' => JenisPengaduanTataPenataan::KEBISINGAN->value,
                'nama_terlapor' => 'Bengkel Jaya Motor',
                'nama_perusahaan_terlapor' => 'Bengkel Jaya Motor',
                'alamat' => 'Jl. Imam Bonjol No. 45, Palu',
                'latitude' => -0.9015,
                'longitude' => 119.8655,
                'deskripsi' => 'Kebisingan mesin di bengkel melebihi batas normal, mengganggu ketenangan warga',
                'status' => StatusPengaduanTataPenataan::MENUNGGU->value,
                'catatan_admin' => null,
                'assigned_user_id' => null,
            ],
        ];

        foreach ($pengaduans as $pengaduan) {
            $created = PengaduanTataPenataan::create($pengaduan);
            
            // Tambahkan foto untuk setiap pengaduan
            for ($i = 1; $i <= 2; $i++) {
                PengaduanTataPenataanFoto::create([
                    'pengaduan_tata_penataan_id' => $created->id,
                    'path_foto' => $this->copyDummyImage("pengaduan{$i}.jpg", 'pengaduan-tata-penataan'),
                ]);
            }
        }
    }

    private function seedSidak(): void
    {
        $objeks = ObjekPengawasan::all();
        $pengaduan = PengaduanTataPenataan::first();

        $sidaks = [
            [
                'objek_pengawasan_id' => $objeks[1]->id,
                'pengaduan_tata_penataan_id' => $pengaduan->id,
                'tanggal_sidak' => Carbon::now()->subDays(5),
                'nama_petugas' => 'Tim Pengawasan DLH Palu',
                'user_id' => User::role('bidang-tata-penataan')->first()->id ?? null,
                'hasil' => HasilSidak::TIDAK_TAAT->value,
                'temuan' => 'Ditemukan pembuangan limbah cair ke sungai tanpa IPAL yang memadai',
                'rekomendasi' => 'Wajib memasang IPAL sesuai standar dan menghentikan pembuangan langsung',
                'status_tindak_lanjut' => StatusTindakLanjutSidak::SELESAI->value,
                'is_jadwal' => false,
                'catatan_jadwal' => null,
            ],
            [
                'objek_pengawasan_id' => $objeks[0]->id,
                'pengaduan_tata_penataan_id' => null,
                'tanggal_sidak' => Carbon::now()->subDays(10),
                'nama_petugas' => 'Tim Pengawasan DLH Palu',
                'user_id' => User::role('bidang-tata-penataan')->first()->id ?? null,
                'hasil' => HasilSidak::TAAT->value,
                'temuan' => 'Pengelolaan limbah sudah sesuai dengan standar',
                'rekomendasi' => 'Pertahankan kualitas pengelolaan limbah',
                'status_tindak_lanjut' => StatusTindakLanjutSidak::SELESAI->value,
                'is_jadwal' => false,
                'catatan_jadwal' => null,
            ],
            [
                'objek_pengawasan_id' => $objeks[2]->id,
                'pengaduan_tata_penataan_id' => null,
                'tanggal_sidak' => Carbon::now()->addDays(7),
                'nama_petugas' => 'Tim Pengawasan DLH Palu',
                'user_id' => User::role('bidang-tata-penataan')->first()->id ?? null,
                'hasil' => null,
                'temuan' => null,
                'rekomendasi' => null,
                'status_tindak_lanjut' => StatusTindakLanjutSidak::BELUM->value,
                'is_jadwal' => true,
                'catatan_jadwal' => 'Sidak rutin triwulanan',
            ],
        ];

        foreach ($sidaks as $sidak) {
            $created = Sidak::create($sidak);
            
            // Tambahkan media untuk sidak yang sudah selesai
            if ($created->hasil) {
                for ($i = 1; $i <= 3; $i++) {
                    SidakMedia::create([
                        'sidak_id' => $created->id,
                        'path' => $this->copyDummyImage("sidak{$i}.jpg", 'sidak'),
                        'tipe' => 'foto',
                    ]);
                }
            }
        }
    }

    private function seedPelanggaran(): void
    {
        $objekMelanggar = ObjekPengawasan::find(2); // CV Kimia Mandiri
        $sidakMelanggar = Sidak::where('hasil', HasilSidak::TIDAK_TAAT->value)->first();

        $pelanggarans = [
            [
                'objek_pengawasan_id' => $objekMelanggar->id,
                'sidak_id' => $sidakMelanggar->id,
                'jenis_pelanggaran' => 'Pembuangan Limbah Cair Tidak Sesuai Standar',
                'pasal_dilanggar' => 'Pasal 60 UU No. 32 Tahun 2009',
                'keterangan' => 'Pembuangan limbah cair industri langsung ke badan air tanpa pengolahan',
            ],
            [
                'objek_pengawasan_id' => $objekMelanggar->id,
                'sidak_id' => $sidakMelanggar->id,
                'jenis_pelanggaran' => 'Tidak Memiliki IPAL yang Memadai',
                'pasal_dilanggar' => 'Pasal 20 UU No. 32 Tahun 2009',
                'keterangan' => 'IPAL tidak berfungsi dengan baik dan tidak memenuhi baku mutu',
            ],
        ];

        foreach ($pelanggarans as $pelanggaran) {
            $created = Pelanggaran::create($pelanggaran);
            
            // Tambahkan media bukti pelanggaran
            for ($i = 1; $i <= 2; $i++) {
                PelanggaranMedia::create([
                    'pelanggaran_id' => $created->id,
                    'path' => $this->copyDummyImage("pelanggaran{$i}.jpg", 'pelanggaran'),
                    'tipe' => 'foto',
                ]);
            }
        }
    }

    private function seedSanksi(): void
    {
        $pelanggarans = Pelanggaran::all();

        foreach ($pelanggarans as $pelanggaran) {
            Sanksi::create([
                'pelanggaran_id' => $pelanggaran->id,
                'jenis_sanksi' => JenisSanksi::TEGURAN_1->value,
                'batas_waktu_perbaikan' => Carbon::now()->addDays(30),
                'status_sanksi' => StatusSanksi::DIBERIKAN->value,
                'surat_path' => $this->copyDummyDoc('surat_teguran.pdf', 'sanksi'),
                'catatan' => 'Diberikan waktu 30 hari untuk melakukan perbaikan sistem IPAL',
            ]);
        }
    }

    private function seedSosialisasi(): void
    {
        $objeks = ObjekPengawasan::all();

        $sosialisasis = [
            [
                'judul' => 'Sosialisasi Pengelolaan Limbah B3 untuk Industri',
                'tanggal' => Carbon::now()->subDays(15),
                'materi' => 'Penjelasan mengenai tata cara pengelolaan limbah B3 yang benar sesuai dengan peraturan perundang-undangan yang berlaku',
                'hasil_evaluasi' => 'Peserta memahami prosedur pengelolaan limbah B3 dengan baik',
            ],
            [
                'judul' => 'Workshop Pengolahan Limbah Cair Industri',
                'tanggal' => Carbon::now()->subDays(30),
                'materi' => 'Pelatihan teknis pengolahan limbah cair untuk industri makanan dan minuman',
                'hasil_evaluasi' => 'Peserta mampu mengaplikasikan teknik pengolahan limbah cair di tempat usahanya',
            ],
            [
                'judul' => 'Sosialisasi Dokumen Lingkungan AMDAL dan UKL-UPL',
                'tanggal' => Carbon::now()->subDays(45),
                'materi' => 'Pemahaman tentang pentingnya dokumen lingkungan dan prosedur pengurusannya',
                'hasil_evaluasi' => 'Peserta memahami kewajiban dokumen lingkungan untuk usahanya',
            ],
        ];

        foreach ($sosialisasis as $index => $sosialisasi) {
            $created = Sosialisasi::create($sosialisasi);
            
            // Tambahkan file materi
            for ($i = 1; $i <= 2; $i++) {
                SosialisasiFile::create([
                    'sosialisasi_id' => $created->id,
                    'path' => $this->copyDummyDoc("sosialisasi_materi_{$index}_{$i}.pdf", 'sosialisasi'),
                    'tipe' => 'materi',
                    'nama' => "Materi {$i} - " . $sosialisasi['judul'],
                ]);
            }

            // Tambahkan foto dokumentasi
            SosialisasiFile::create([
                'sosialisasi_id' => $created->id,
                'path' => $this->copyDummyImage("sosialisasi_foto_{$index}.jpg", 'sosialisasi'),
                'tipe' => 'dokumentasi',
                'nama' => 'Dokumentasi Kegiatan',
            ]);

            // Tambahkan peserta dari objek pengawasan
            foreach ($objeks->take(3) as $objek) {
                SosialisasiPeserta::create([
                    'sosialisasi_id' => $created->id,
                    'objek_pengawasan_id' => $objek->id,
                    'sertifikat_path' => $this->copyDummyDoc("sertifikat_{$objek->id}.pdf", 'sosialisasi'),
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
