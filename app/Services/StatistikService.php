<?php

namespace App\Services;

use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\WebsiteVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistikService
{
    private static array $tableCache = [];

    private function hasTableCached(string $table): bool
    {
        if (array_key_exists($table, self::$tableCache)) {
            return self::$tableCache[$table];
        }

        // Cache hasil introspeksi skema 1 jam (sekarang di file cache, bukan Neon).
        return self::$tableCache[$table] = Cache::remember(
            'schema:has:' . $table,
            now()->addHour(),
            fn () => Schema::hasTable($table)
        );
    }

    public function pengunjungHariIni(): int
    {
        if (! $this->hasTableCached('website_visit')) {
            return 0;
        }

        return WebsiteVisit::query()
            ->whereDate('visit_date', today())
            ->count();
    }

    public function totalPengunjung(): int
    {
        if (! $this->hasTableCached('website_visit')) {
            return 0;
        }

        return WebsiteVisit::query()->count();
    }

    public function totalPelapor(): int
    {
        $total = 0;

        if ($this->hasTableCached('pengaduan_pengendalian')) {
            $total += PengaduanPengendalian::query()->count();
        }

        if ($this->hasTableCached('pengaduan_sampah')) {
            $total += PengaduanSampah::query()->count();
        }

        if ($this->hasTableCached('pengaduan_rth')) {
            $total += PengaduanRth::query()->count();
        }

        if ($this->hasTableCached('pengaduan_tata_penataan')) {
            $total += PengaduanTataPenataan::query()->count();
        }

        return $total;
    }

    public function totalPengajuan(): int
    {
        $total = 0;

        if ($this->hasTableCached('permohonan_rekomendasis')) {
            $total += PermohonanRekomendasi::query()->count();
        }

        if ($this->hasTableCached('registrasi_usaha_lb3')) {
            $total += RegistrasiUsahaLb3::query()->count();
        }

        if ($this->hasTableCached('pengajuan_rintek_pertek')) {
            $total += PengajuanRintekPertek::query()->count();
        }

        if ($this->hasTableCached('permohonan_pinjam_taman')) {
            $total += PermohonanPinjamTaman::query()->count();
        }

        return $total;
    }

    public function summary(): array
    {
        // Ringkasan bersifat statistik — cache 15 menit agar tidak query tiap request.
        return Cache::remember('statistik:summary', now()->addMinutes(15), function () {
            return [
                'pengunjung_hari_ini' => $this->pengunjungHariIni(),
                'total_pengunjung' => $this->totalPengunjung(),
                'total_pelapor' => $this->totalPelapor(),
                'total_pengajuan' => $this->totalPengajuan(),
            ];
        });
    }

    /**
     * Monthly ticket counts grouped by kategori/bidang for multi-line chart.
     */
    public function trenPerBidang(array $allowedGroups = []): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));
        $labels = $months->map(fn ($m) => $m->translatedFormat('M Y'))->all();

        $bidangMap = [
            'pengendalian' => PengaduanPengendalian::class,
            'sampah-lb3' => PengaduanSampah::class,
            'rth' => PengaduanRth::class,
            'tata-penataan' => PengaduanTataPenataan::class,
        ];

        $datasets = [];
        foreach ($allowedGroups as $group) {
            $modelClass = $bidangMap[$group] ?? null;
            if (! $modelClass) continue;

            $data = $months->map(function ($m) use ($modelClass) {
                return $modelClass::query()
                    ->whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->count();
            })->all();

            $datasets[$group] = $data;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

}
