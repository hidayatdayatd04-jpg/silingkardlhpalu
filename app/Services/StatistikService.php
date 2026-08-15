<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\WebsiteVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistikService
{
    private static array $tableCache = [];

    private function hasTableCached(string $table): bool
    {
        return self::$tableCache[$table] ??= Schema::hasTable($table);
    }

    public function pengunjungHariIni(): int
    {
        if (! $this->hasTableCached('website_visits')) {
            return 0;
        }

        return WebsiteVisit::query()
            ->whereDate('visit_date', today())
            ->count();
    }

    public function totalPengunjung(): int
    {
        if (! $this->hasTableCached('website_visits')) {
            return 0;
        }

        return WebsiteVisit::query()->count();
    }

    public function totalPelapor(): int
    {
        if (! $this->hasTableCached('laporans')) {
            return 0;
        }

        return Laporan::query()->count();
    }

    public function totalPengajuan(): int
    {
        $total = 0;

        if ($this->hasTableCached('permohonan_rekomendasis')) {
            $total += PermohonanRekomendasi::query()->count();
        }

        if ($this->hasTableCached('registrasi_usaha_lb3s')) {
            $total += RegistrasiUsahaLb3::query()->count();
        }

        if ($this->hasTableCached('pengajuan_rintek_perteks')) {
            $total += PengajuanRintekPertek::query()->count();
        }

        if ($this->hasTableCached('permohonan_pinjam_tamans')) {
            $total += PermohonanPinjamTaman::query()->count();
        }

        return $total;
    }

    public function summary(): array
    {
        return [
            'pengunjung_hari_ini' => $this->pengunjungHariIni(),
            'total_pengunjung' => $this->totalPengunjung(),
            'total_pelapor' => $this->totalPelapor(),
            'total_pengajuan' => $this->totalPengajuan(),
        ];
    }

    /**
     * Monthly ticket counts grouped by kategori/bidang for multi-line chart.
     */
    public function trenPerBidang(array $allowedGroups = []): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));
        $labels = $months->map(fn ($m) => $m->translatedFormat('M Y'))->all();

        $bidangMap = [
            'pengendalian' => 'pengendalian',
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
        ];

        $datasets = [];
        foreach ($allowedGroups as $group) {
            $bidang = $bidangMap[$group] ?? null;
            if (! $bidang) continue;

            $data = $months->map(function ($m) use ($bidang) {
                return Laporan::where('bidang', $bidang)
                    ->whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->count();
            })->all();

            $datasets[$bidang] = $data;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

}
