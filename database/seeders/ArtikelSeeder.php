<?php

namespace Database\Seeders;

use App\Enums\ArtikelKategori;
use App\Enums\ArtikelStatus;
use App\Models\Artikel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::role('superadmin')->first();

        $artikels = [
            // Artikel RTH
            [
                'judul' => 'Pemerintah Kota Palu Resmikan Taman Nosarara Nosabatutu sebagai Taman Kota Terbesar',
                'konten' => $this->generateKonten('rth_taman_besar'),
                'kategori' => ArtikelKategori::RTH->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(30),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Program Penanaman 10.000 Pohon di Sepanjang Jalan Protokol Kota Palu',
                'konten' => $this->generateKonten('rth_penanaman'),
                'kategori' => ArtikelKategori::RTH->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(25),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'DLH Palu Ajak Masyarakat Jaga Kelestarian Taman Kota',
                'konten' => $this->generateKonten('rth_ajakan'),
                'kategori' => ArtikelKategori::RTH->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(20),
                'user_id' => $superAdmin->id,
            ],

            // Artikel Sampah & LB3
            [
                'judul' => 'Bank Sampah Mandiri Raih Penghargaan Terbaik Se-Sulawesi Tengah',
                'konten' => $this->generateKonten('sampah_bank'),
                'kategori' => ArtikelKategori::SAMPAH_LB3->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(18),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'DLH Palu Sosialisasi Pengelolaan Limbah B3 di Kawasan Industri',
                'konten' => $this->generateKonten('sampah_lb3'),
                'kategori' => ArtikelKategori::SAMPAH_LB3->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(15),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Jadwal Pengangkutan Sampah Terbaru untuk Wilayah Palu Barat',
                'konten' => $this->generateKonten('sampah_jadwal'),
                'kategori' => ArtikelKategori::SAMPAH_LB3->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(12),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Kota Palu Targetkan Pengurangan Sampah 30% pada Tahun 2027',
                'konten' => $this->generateKonten('sampah_target'),
                'kategori' => ArtikelKategori::SAMPAH_LB3->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(10),
                'user_id' => $superAdmin->id,
            ],

            // Artikel Pengendalian
            [
                'judul' => 'DLH Palu Lakukan Pengawasan Kualitas Air Sungai Palu',
                'konten' => $this->generateKonten('pengendalian_air'),
                'kategori' => ArtikelKategori::PENGENDALIAN->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(8),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Sosialisasi Pencegahan Pencemaran Udara untuk Industri di Palu',
                'konten' => $this->generateKonten('pengendalian_udara'),
                'kategori' => ArtikelKategori::PENGENDALIAN->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(6),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'DLH Palu Tangani 150 Pengaduan Lingkungan Sepanjang 2026',
                'konten' => $this->generateKonten('pengendalian_pengaduan'),
                'kategori' => ArtikelKategori::PENGENDALIAN->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(5),
                'user_id' => $superAdmin->id,
            ],

            // Artikel Tata Penataan
            [
                'judul' => 'Sidak Objek Pengawasan: 15 Perusahaan Taat Lingkungan',
                'konten' => $this->generateKonten('tata_sidak'),
                'kategori' => ArtikelKategori::TATA_PENATAAN->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(4),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'DLH Palu Perketat Pengawasan Izin Lingkungan Usaha',
                'konten' => $this->generateKonten('tata_izin'),
                'kategori' => ArtikelKategori::TATA_PENATAAN->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(3),
                'user_id' => $superAdmin->id,
            ],

            // Artikel Umum
            [
                'judul' => 'DLH Palu Raih Penghargaan Adipura untuk Ketiga Kalinya',
                'konten' => $this->generateKonten('umum_adipura'),
                'kategori' => ArtikelKategori::UMUM->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(2),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Peringatan Hari Lingkungan Hidup Sedunia 2026 di Palu',
                'konten' => $this->generateKonten('umum_hari_lingkungan'),
                'kategori' => ArtikelKategori::UMUM->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now()->subDays(1),
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Aplikasi Lapor Lingkungan DLH Palu Telah Diunduh 5000 Kali',
                'konten' => $this->generateKonten('umum_aplikasi'),
                'kategori' => ArtikelKategori::UMUM->value,
                'status' => ArtikelStatus::PUBLISHED->value,
                'tanggal_publish' => Carbon::now(),
                'user_id' => $superAdmin->id,
            ],

            // Artikel Draft (belum publish)
            [
                'judul' => 'Rencana Pembangunan TPST Regional di Palu Tahun 2027',
                'konten' => $this->generateKonten('draft_tpst'),
                'kategori' => ArtikelKategori::SAMPAH_LB3->value,
                'status' => ArtikelStatus::DRAFT->value,
                'tanggal_publish' => null,
                'user_id' => $superAdmin->id,
            ],
            [
                'judul' => 'Program Kampung Iklim di 10 Kelurahan Kota Palu',
                'konten' => $this->generateKonten('draft_kampung'),
                'kategori' => ArtikelKategori::UMUM->value,
                'status' => ArtikelStatus::DRAFT->value,
                'tanggal_publish' => null,
                'user_id' => $superAdmin->id,
            ],
        ];

        foreach ($artikels as $artikel) {
            $created = Artikel::create([
                'judul' => $artikel['judul'],
                'konten' => $artikel['konten'],
                'kategori' => $artikel['kategori'],
                'status' => $artikel['status'],
                'tanggal_publish' => $artikel['tanggal_publish'],
                'user_id' => $artikel['user_id'],
                'thumbnail' => $this->copyThumbnail(),
            ]);

            $this->command->info("✓ Artikel created: {$created->judul}");
        }

        $this->command->info("Total ".count($artikels)." artikel berhasil dibuat!");
    }

    private function generateKonten(string $tipe): string
    {
        return match ($tipe) {
            'rth_taman_besar' => "<h2>Taman Kota Terbesar di Palu</h2>
<p>Pemerintah Kota Palu melalui Dinas Lingkungan Hidup resmi meresmikan Taman Nosarara Nosabatutu sebagai taman kota terbesar di Palu dengan luas mencapai 5 hektar. Taman ini dilengkapi dengan berbagai fasilitas seperti jogging track, area bermain anak, dan spot foto instagramable.</p>
<p>Walikota Palu dalam sambutannya menyampaikan bahwa pembangunan taman ini merupakan komitmen pemerintah untuk meningkatkan Ruang Terbuka Hijau (RTH) di Kota Palu yang saat ini sudah mencapai 25% dari luas wilayah kota.</p>
<h3>Fasilitas Taman</h3>
<ul>
<li>Jogging track sepanjang 2 km</li>
<li>Playground untuk anak-anak</li>
<li>Gazebo dan bangku taman</li>
<li>Penerangan taman LED</li>
<li>Area parkir luas</li>
</ul>
<p>Taman ini buka setiap hari mulai pukul 05.00 - 22.00 WITA dan gratis untuk umum.</p>",

            'rth_penanaman' => "<h2>Program Penanaman Pohon Massal</h2>
<p>DLH Palu meluncurkan program ambisius untuk menanam 10.000 pohon di sepanjang jalan protokol Kota Palu dalam rangka meningkatkan tutupan hijau dan mengurangi polusi udara.</p>
<p>Program ini melibatkan partisipasi aktif dari masyarakat, sekolah-sekolah, dan instansi pemerintah. Jenis pohon yang ditanam antara lain trembesi, mahoni, flamboyan, dan tabebuya yang cocok untuk iklim Palu.</p>
<h3>Target dan Manfaat</h3>
<p>Program ini ditargetkan selesai dalam 6 bulan dengan melibatkan 500 relawan. Manfaat yang diharapkan:</p>
<ul>
<li>Mengurangi suhu udara kota hingga 2-3 derajat celcius</li>
<li>Menyerap polusi udara dari kendaraan</li>
<li>Meningkatkan estetika kota</li>
<li>Memberikan keteduhan untuk pejalan kaki</li>
</ul>",

            'rth_ajakan' => "<h2>Mari Jaga Taman Kota Bersama</h2>
<p>DLH Palu mengajak seluruh masyarakat untuk bersama-sama menjaga kelestarian dan kebersihan taman-taman kota yang telah dibangun dengan dana APBD yang tidak sedikit.</p>
<p>Kepala DLH Palu menyampaikan keprihatinannya atas masih ditemukannya vandalisme di beberapa taman kota. \"Taman ini milik kita bersama, mari kita jaga untuk anak cucu kita,\" ujarnya.</p>
<h3>Apa yang Bisa Kita Lakukan?</h3>
<ul>
<li>Tidak membuang sampah sembarangan</li>
<li>Tidak merusak fasilitas taman</li>
<li>Tidak mencoret-coret bangku dan dinding</li>
<li>Melaporkan kerusakan ke DLH melalui aplikasi</li>
<li>Ikut serta dalam program adopsi pohon</li>
</ul>",

            'sampah_bank' => "<h2>Prestasi Membanggakan Bank Sampah Mandiri</h2>
<p>Bank Sampah Mandiri yang berlokasi di Kelurahan Lere berhasil meraih penghargaan sebagai Bank Sampah Terbaik Se-Sulawesi Tengah dalam ajang Anugerah Adipura 2026.</p>
<p>Bank sampah yang dikelola oleh ibu-ibu PKK ini telah beroperasi selama 5 tahun dan memiliki 250 nasabah aktif. Dalam setahun, bank sampah ini mampu mengelola 50 ton sampah yang kemudian didaur ulang atau dijual ke pengepul.</p>
<h3>Hasil Pengelolaan</h3>
<p>Penghasilan yang diperoleh nasabah rata-rata Rp 200.000 per bulan, sedangkan untuk pengelola bisa mencapai Rp 3.000.000 per bulan. Dana ini berasal dari penjualan sampah yang sudah dipilah.</p>",

            'sampah_lb3' => "<h2>Pentingnya Pengelolaan Limbah B3 yang Benar</h2>
<p>DLH Palu mengadakan sosialisasi pengelolaan Limbah Bahan Berbahaya dan Beracun (B3) kepada 50 perusahaan di kawasan industri Palu. Sosialisasi ini penting mengingat masih banyak perusahaan yang belum memahami prosedur pengelolaan limbah B3.</p>
<p>Kepala Bidang Sampah dan LB3 menjelaskan bahwa limbah B3 yang tidak dikelola dengan baik dapat mencemari lingkungan dan membahayakan kesehatan masyarakat.</p>
<h3>Jenis Limbah B3</h3>
<ul>
<li>Aki bekas kendaraan</li>
<li>Oli bekas</li>
<li>Kemasan bahan kimia</li>
<li>Limbah medis</li>
<li>Lampu neon bekas</li>
</ul>
<p>Perusahaan yang tidak mengelola limbah B3 dengan benar dapat dikenakan sanksi administratif hingga pidana.</p>",

            'sampah_jadwal' => "<h2>Jadwal Pengangkutan Sampah Terbaru</h2>
<p>DLH Palu mengumumkan jadwal pengangkutan sampah terbaru untuk wilayah Palu Barat yang berlaku mulai 1 Agustus 2026. Perubahan jadwal ini dilakukan untuk meningkatkan efisiensi pengangkutan.</p>
<h3>Jadwal per Kelurahan:</h3>
<ul>
<li>Kelurahan Lere: Senin, Rabu, Jumat (pukul 06.00-10.00)</li>
<li>Kelurahan Besusu: Selasa, Kamis, Sabtu (pukul 06.00-10.00)</li>
<li>Kelurahan Birobuli: Senin, Rabu, Jumat (pukul 10.00-14.00)</li>
<li>Kelurahan Petobo: Selasa, Kamis, Sabtu (pukul 10.00-14.00)</li>
</ul>
<p>Masyarakat diminta untuk mengeluarkan sampah maksimal 30 menit sebelum armada datang dan sudah dalam kondisi terpilah (organik dan anorganik).</p>",

            'sampah_target' => "<h2>Target Pengurangan Sampah 30%</h2>
<p>Pemerintah Kota Palu menargetkan pengurangan sampah hingga 30% pada tahun 2027 melalui berbagai program seperti bank sampah, composting, dan kampanye reduce-reuse-recycle.</p>
<p>Saat ini volume sampah yang dihasilkan warga Palu mencapai 400 ton per hari. Dengan target pengurangan 30%, diharapkan volume sampah yang masuk ke TPA Kawatuna berkurang menjadi 280 ton per hari.</p>
<h3>Strategi Pencapaian:</h3>
<ul>
<li>Pengembangan 50 bank sampah baru</li>
<li>Program composting di 100 rumah tangga</li>
<li>Kampanye penggunaan tas belanja ramah lingkungan</li>
<li>Larangan kantong plastik di supermarket</li>
</ul>",

            'pengendalian_air' => "<h2>Monitoring Kualitas Air Sungai Palu</h2>
<p>DLH Palu melakukan pengawasan rutin kualitas air Sungai Palu di 10 titik pemantauan. Hasil pemantauan menunjukkan bahwa kualitas air masih dalam batas wajar dengan status cemar ringan di beberapa titik.</p>
<p>Parameter yang dipantau meliputi pH, BOD, COD, TSS, dan logam berat. Pemantauan dilakukan setiap 3 bulan sekali sesuai dengan peraturan perundangan yang berlaku.</p>
<h3>Tindak Lanjut</h3>
<p>DLH akan melakukan teguran kepada industri yang diduga membuang limbah ke sungai tanpa pengolahan yang memadai. Sidak mendadak juga akan dilakukan untuk memastikan kepatuhan pelaku usaha.</p>",

            'pengendalian_udara' => "<h2>Sosialisasi Pencegahan Pencemaran Udara</h2>
<p>DLH Palu mengadakan sosialisasi pencegahan pencemaran udara kepada 30 industri yang beroperasi di Kota Palu. Sosialisasi ini membahas tentang baku mutu emisi udara dan kewajiban melakukan pemantauan kualitas udara.</p>
<p>Industri wajib melakukan uji emisi cerobong setiap 6 bulan sekali dan melaporkan hasilnya ke DLH Palu. Industri yang tidak patuh dapat dikenakan sanksi berupa teguran tertulis hingga pencabutan izin.</p>",

            'pengendalian_pengaduan' => "<h2>150 Pengaduan Lingkungan Ditangani</h2>
<p>Sepanjang tahun 2026, DLH Palu telah menangani 150 pengaduan lingkungan dari masyarakat melalui berbagai saluran seperti aplikasi, telepon, dan datang langsung ke kantor.</p>
<p>Jenis pengaduan terbanyak adalah tentang pengelolaan sampah (40%), pencemaran air (30%), dan pencemaran udara (20%). Dari 150 pengaduan, 120 telah selesai ditindaklanjuti dan 30 masih dalam proses.</p>
<h3>Cara Menyampaikan Pengaduan:</h3>
<ul>
<li>Aplikasi Lapor DLH Palu</li>
<li>WhatsApp: 0812-3456-7890</li>
<li>Email: pengaduan@dlhpalu.go.id</li>
<li>Datang langsung ke Kantor DLH Palu</li>
</ul>",

            'tata_sidak' => "<h2>Hasil Sidak Objek Pengawasan</h2>
<p>Tim DLH Palu melakukan sidak mendadak ke 20 perusahaan yang menjadi objek pengawasan. Dari hasil sidak, 15 perusahaan dinyatakan taat lingkungan, 3 perlu pembinaan, dan 2 tidak taat.</p>
<p>Perusahaan yang tidak taat akan diberikan teguran tertulis dan diminta untuk melakukan perbaikan dalam waktu 30 hari. Jika tidak ada perbaikan, akan dilakukan proses hukum lebih lanjut.</p>",

            'tata_izin' => "<h2>Pengawasan Izin Lingkungan Diperketat</h2>
<p>DLH Palu memperketat pengawasan terhadap izin lingkungan usaha di Kota Palu. Setiap usaha wajib memiliki dokumen lingkungan seperti AMDAL, UKL-UPL, atau SPPL sesuai dengan skala usahanya.</p>
<p>Saat ini terdapat 500 usaha yang telah memiliki dokumen lingkungan dan 50 usaha yang sedang dalam proses pengurusan. Usaha yang tidak memiliki dokumen lingkungan tidak akan diberikan izin operasional.</p>",

            'umum_adipura' => "<h2>Palu Raih Adipura Ketiga Kalinya</h2>
<p>Kota Palu kembali meraih penghargaan Adipura untuk ketiga kalinya berturut-turut. Penghargaan ini diberikan oleh Kementerian Lingkungan Hidup dan Kehutanan atas keberhasilan Kota Palu dalam menjaga kebersihan dan pengelolaan lingkungan.</p>
<p>Walikota Palu menyampaikan terima kasih kepada seluruh jajaran Pemkot Palu, terutama DLH, dan masyarakat yang telah mendukung program kebersihan kota.</p>",

            'umum_hari_lingkungan' => "<h2>Peringatan Hari Lingkungan Hidup Sedunia</h2>
<p>DLH Palu menggelar rangkaian acara dalam rangka memperingati Hari Lingkungan Hidup Sedunia yang jatuh pada 5 Juni. Acara meliputi penanaman pohon, car free day, pameran, dan lomba kebersihan lingkungan.</p>
<p>Tema tahun ini adalah \"Restorasi Ekosistem\" yang mengajak masyarakat untuk berperan aktif dalam pemulihan ekosistem yang rusak.</p>",

            'umum_aplikasi' => "<h2>Aplikasi Lapor Lingkungan Populer</h2>
<p>Aplikasi Lapor Lingkungan DLH Palu telah diunduh lebih dari 5.000 kali sejak diluncurkan 6 bulan lalu. Aplikasi ini memudahkan masyarakat untuk melaporkan permasalahan lingkungan seperti sampah menumpuk, pencemaran, dan kerusakan fasilitas taman.</p>
<p>Aplikasi tersedia gratis di Google Play Store dan App Store dengan rating 4.5 dari 5 bintang.</p>",

            'draft_tpst' => "<h2>Rencana Pembangunan TPST Regional</h2>
<p>Pemerintah Kota Palu merencanakan pembangunan Tempat Pengolahan Sampah Terpadu (TPST) Regional dengan kapasitas 500 ton per hari. TPST ini akan menggunakan teknologi waste to energy yang dapat mengubah sampah menjadi listrik.</p>
<p>Pembangunan ditargetkan dimulai tahun 2027 dengan dana dari APBN dan APBD. Lokasi TPST rencananya di wilayah Palu Timur dengan luas lahan 10 hektar.</p>",

            'draft_kampung' => "<h2>Program Kampung Iklim</h2>
<p>DLH Palu akan meluncurkan Program Kampung Iklim (ProKlim) di 10 kelurahan sebagai pilot project. Program ini bertujuan meningkatkan ketahanan masyarakat terhadap perubahan iklim.</p>
<p>Kegiatan ProKlim meliputi penghijauan, pengelolaan sampah, biopori, komposting, dan edukasi lingkungan. Setiap kelurahan akan mendapat pendampingan dan bantuan peralatan dari DLH.</p>",

            default => "<p>Konten artikel default. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>",
        };
    }

    private function copyThumbnail(): string
    {
        $thumbnails = [
            'admin/artikel/artikel_1_1770171407_258c4ec0b24d3b7a51e8.jpeg',
            'admin/artikel/artikel_2_1758609074_3f3be49187a069d17e29.jpeg',
            'admin/artikel/artikel_3_1758609535_d868768ae56e87e7bab2.jpeg',
            'admin/artikel/artikel_4_1758607983_6a4d5bc6a6216d632cb3.jpeg',
            'admin/artikel/artikel_5_1758608216_16de0f778d43659ab8de.jpeg',
            'admin/artikel/artikel_6_1758608465_cd1e26fc028518953ec2.jpeg',
            'admin/artikel/artikel_7_1758599573_af4a32fcfb54a24894de.jpeg',
            'admin/artikel/artikel_8_1758599861_56d04b423eb3e256b36a.jpeg',
            'admin/artikel/artikel_9_1757654092_959a50b1f3ad383ba5ac.jpeg',
            'admin/artikel/artikel_10_1757402634_a4119a1ae26ece569b1c.png',
            'admin/artikel/artikel_11_1756689123_39ce8a4c4cb4cac7f978.jpeg',
            'admin/artikel/artikel_12_1756689241_a51c3fc08f226be97877.jpeg',
        ];

        $filename = $thumbnails[array_rand($thumbnails)];

        if (File::exists(storage_path('app/public/' . $filename))) {
            return $filename;
        }

        return 'placeholder.jpg';
    }
}
