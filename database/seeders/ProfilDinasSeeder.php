<?php

namespace Database\Seeders;

use App\Models\ProfilDinas;
use Illuminate\Database\Seeder;

class ProfilDinasSeeder extends Seeder
{
    public function run(): void
    {
        ProfilDinas::query()->delete();

        ProfilDinas::create([
            'visi' => '<p>Terwujudnya Kota Palu yang Bersih, Hijau, Berkelanjutan, dan Tangguh terhadap Bencana Lingkungan.</p>',
            'misi' => '<ol>
<li>Meningkatkan kualitas pelayanan kebersihan dan pengelolaan sampah yang terintegrasi.</li>
<li>Mengoptimalkan pemeliharaan Ruang Terbuka Hijau (RTH) dan mitigasi risiko pohon pelindung.</li>
<li>Meningkatkan kesadaran dan partisipasi aktif masyarakat dalam pelestarian lingkungan hidup.</li>
<li>Mewujudkan tata kelola pemerintahan yang baik (Good Corporate Governance) berbasis teknologi informasi.</li>
</ol>',
            'tugas_fungsi' => '<h3>Tugas Pokok</h3>
<p>Melaksanakan urusan pemerintahan daerah di bidang lingkungan hidup, persampahan, kebersihan, serta pengelolaan ruang terbuka hijau berdasarkan asas otonomi dan tugas pembantuan.</p>
<h3>Fungsi Utama</h3>
<ul>
<li>Perumusan kebijakan teknis operasional di bidang perlindungan dan pengelolaan lingkungan hidup.</li>
<li>Pelaksanaan pengawasan, pengendalian, dan pemulihan kualitas lingkungan terhadap dampak pembangunan.</li>
<li>Penyelenggaraan pengelolaan persampahan, kebersihan wilayah, dan penataan pertamanan kota.</li>
<li>Pembinaan dan pemberdayaan peran serta masyarakat dalam pemeliharaan lingkungan hidup.</li>
</ul>',
            'struktur_organisasi_image' => null,
            'pejabats' => [
                [
                    'nama' => 'Mohamad Arif, S.STP., M.Si',
                    'jabatan' => 'Kepala Dinas Lingkungan Hidup Kota Palu',
                    'foto' => null,
                ],
                [
                    'nama' => 'Sekretaris Dinas',
                    'jabatan' => 'Sekretaris Dinas Lingkungan Hidup',
                    'foto' => null,
                ],
                [
                    'nama' => 'Kepala Bidang Pengendalian',
                    'jabatan' => 'Kepala Bidang Pengendalian Dampak Lingkungan',
                    'foto' => null,
                ],
                [
                    'nama' => 'Kepala Bidang Sampah & LB3',
                    'jabatan' => 'Kepala Bidang Pengelolaan Sampah dan LB3',
                    'foto' => null,
                ],
            ],
        ]);
    }
}
