<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Artikel;
use App\Models\Laporan;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\User;
use App\Models\WebsiteVisit;
use App\Support\Admin\AdminRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $allowedGroups = $user->allowedGroups();

        $cards = $this->buildCards($allowedGroups);
        $recent = $this->buildRecent($allowedGroups);

        return view('admin.dashboard', [
            'groups'       => AdminRegistry::forUser($user),
            'cards'        => $cards,
            'recent'       => $recent,
            'activeUsers'  => $user->isSuperadmin() ? User::where('is_active', true)->count() : null,
            'visits'       => $user->isSuperadmin() && class_exists(WebsiteVisit::class) ? WebsiteVisit::count() : null,
            'allowedGroups' => $allowedGroups,
            'charts'       => $this->buildCharts($allowedGroups),
            'mapLocations' => $user->isSuperadmin() ? app(\App\Services\StatistikService::class)->lokasiPengaduan($allowedGroups) : [],
            'activityFeed' => $user->isSuperadmin()
                ? ActivityLog::with('user')->latest()->take(8)->get()
                : collect(),
        ]);
    }

    protected function buildCards(array $allowedGroups): array
    {
        $cards = [];

        if (in_array('pengendalian', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Pengendalian', 'value' => Laporan::where('bidang', 'pengendalian')->count(), 'tone' => 'emerald', 'icon' => 'alert-circle'];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Sampah', 'value' => Laporan::where('bidang', 'sampah-lb3')->count(), 'tone' => 'sky', 'icon' => 'recycle'];
            $cards[] = ['label' => 'Registrasi LB3', 'value' => RegistrasiUsahaLb3::count(), 'tone' => 'amber', 'icon' => 'building'];
        }
        if (in_array('rth', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan RTH', 'value' => Laporan::where('bidang', 'rth')->count(), 'tone' => 'teal', 'icon' => 'tree'];
            $cards[] = ['label' => 'Permohonan Rekomendasi', 'value' => PermohonanRekomendasi::count(), 'tone' => 'indigo', 'icon' => 'file-text'];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Tata Penataan', 'value' => \App\Models\PengaduanTataPenataan::count(), 'tone' => 'purple', 'icon' => 'building'];
        }
        if (in_array('konten', $allowedGroups)) {
            $cards[] = ['label' => 'Artikel', 'value' => Artikel::count(), 'tone' => 'rose', 'icon' => 'file-text'];
        }

        return $cards;
    }

    protected function buildRecent(array $allowedGroups): array
    {
        $recent = [];

        if (array_intersect($allowedGroups, ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'])) {
            $recent['laporan'] = Laporan::with([])->latest()->take(5)->get();
        }
        if (in_array('rth', $allowedGroups)) {
            $recent['permohonan'] = PermohonanRekomendasi::with([])->latest()->take(5)->get();
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $recent['registrasi_lb3'] = RegistrasiUsahaLb3::with([])->latest()->take(5)->get();
        }

        return $recent;
    }

    /**
     * Data agregat untuk chart (line tren, bar per modul, doughnut status).
     */
    protected function buildCharts(array $allowedGroups): array
    {
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        // ── Tren 6 bulan terakhir (line) ──
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));
        $trendLabels = $months->map(fn ($m) => $m->translatedFormat('M Y'))->all();

        $trendData = $months->map(function ($m) use ($bidangValues) {
            $q = Laporan::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month);
            if (! empty($bidangValues)) {
                $q->whereIn('bidang', $bidangValues);
            }

            return $q->count();
        })->all();

        // ── Jumlah data per modul (bar) ──
        $barLabels = [];
        $barData = [];
        foreach ($this->buildCards($allowedGroups) as $card) {
            $barLabels[] = $card['label'];
            $barData[] = $card['value'];
        }

        // ── Distribusi status pengaduan (doughnut) ──
        $statusQuery = Laporan::query();
        if (! empty($bidangValues)) {
            $statusQuery->whereIn('bidang', $bidangValues);
        }
        $statusRows = $statusQuery->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'trend' => [
                'labels' => $trendLabels,
                'data'   => $trendData,
            ],
            'modules' => [
                'labels' => $barLabels,
                'data'   => $barData,
            ],
            'status' => [
                'labels' => $statusRows->keys()->map(fn ($s) => $s ?: 'Tanpa Status')->all(),
                'data'   => $statusRows->values()->all(),
            ],
            'trendPerBidang' => app(\App\Services\StatistikService::class)->trenPerBidang($allowedGroups),
        ];
    }
}
