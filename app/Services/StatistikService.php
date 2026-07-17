<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\PerizinanTebangPohon;
use App\Models\RegistrasiUsahaLb3;
use App\Models\WebsiteVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistikService
{
    public function pengunjungHariIni(): int
    {
        if (! Schema::hasTable('website_visits')) {
            return 0;
        }

        return WebsiteVisit::query()
            ->whereDate('visit_date', today())
            ->count();
    }

    public function totalPengunjung(): int
    {
        if (! Schema::hasTable('website_visits')) {
            return 0;
        }

        return WebsiteVisit::query()->count();
    }

    public function totalPelapor(): int
    {
        if (! Schema::hasTable('laporans')) {
            return 0;
        }

        return Laporan::query()->count();
    }

    public function totalPengajuan(): int
    {
        $total = 0;

        if (Schema::hasTable('permohonan_rekomendasis')) {
            $total += PermohonanRekomendasi::query()->count();
        }

        if (Schema::hasTable('registrasi_usaha_lb3s')) {
            $total += RegistrasiUsahaLb3::query()->count();
        }

        if (Schema::hasTable('pengajuan_rintek_perteks')) {
            $total += PengajuanRintekPertek::query()->count();
        }

        if (Schema::hasTable('perizinan_tebang_pohons')) {
            $total += PerizinanTebangPohon::query()->count();
        }

        if (Schema::hasTable('permohonan_pinjam_tamans')) {
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

    /**
     * Complaint locations for map overlay.
     */
    public function lokasiPengaduan(array $allowedGroups = []): array
    {
        $bidangMap = [
            'pengendalian' => 'pengendalian',
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
        ];

        $bidangFilters = [];
        foreach ($allowedGroups as $group) {
            if (isset($bidangMap[$group])) {
                $bidangFilters[] = $bidangMap[$group];
            }
        }

        $query = Laporan::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (! empty($bidangFilters)) {
            $query->whereIn('bidang', $bidangFilters);
        }

        return $query->select('nomor_tiket', 'bidang', 'jenis_pengaduan', 'status', 'latitude', 'longitude', 'alamat')
            ->latest()
            ->take(200)
            ->get()
            ->toArray();
    }
}
