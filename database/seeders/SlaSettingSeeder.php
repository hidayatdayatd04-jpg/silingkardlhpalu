<?php

namespace Database\Seeders;

use App\Models\SlaSetting;
use Illuminate\Database\Seeder;

class SlaSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['model_type' => \App\Models\Laporan::class, 'kategori' => 'pengendalian', 'target_hari' => 3],
            ['model_type' => \App\Models\Laporan::class, 'kategori' => 'sampah', 'target_hari' => 3],
            ['model_type' => \App\Models\Laporan::class, 'kategori' => 'rth', 'target_hari' => 3],
            ['model_type' => \App\Models\Laporan::class, 'kategori' => 'tata-penataan', 'target_hari' => 3],
            ['model_type' => \App\Models\PermohonanRekomendasi::class, 'kategori' => null, 'target_hari' => 14],
            ['model_type' => \App\Models\PengajuanRintekPertek::class, 'kategori' => null, 'target_hari' => 14],
            ['model_type' => \App\Models\PerizinanTebangPohon::class, 'kategori' => null, 'target_hari' => 7],
            ['model_type' => \App\Models\PermohonanPinjamTaman::class, 'kategori' => null, 'target_hari' => 7],
            ['model_type' => \App\Models\RegistrasiUsahaLb3::class, 'kategori' => null, 'target_hari' => 14],
        ];

        foreach ($settings as $setting) {
            SlaSetting::updateOrCreate(
                ['model_type' => $setting['model_type'], 'kategori' => $setting['kategori']],
                ['target_hari' => $setting['target_hari']]
            );
        }
    }
}
