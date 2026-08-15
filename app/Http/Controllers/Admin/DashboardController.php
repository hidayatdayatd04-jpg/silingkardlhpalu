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
use App\Services\StatistikService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $allowedGroups = $user->allowedGroups();
        $statistik = app(StatistikService::class);
        $isSuperadmin = $user->isSuperadmin();

        // Agregat Laporan dihitung SEKALI (2 query) lalu dipakai semua kartu/chart.
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));
        $agg = $this->aggregateLaporan($bidangValues);

        // Statistik berat di-cache 60 dtk agar navigasi berulang ke dashboard instan.
        $cacheKey = 'dashboard:' . $user->id . ':' . md5(implode(',', $allowedGroups));
        $cached = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($user, $allowedGroups, $isSuperadmin, $statistik, $agg) {
            return [
                'cards'        => $this->buildCards($allowedGroups, $user, $agg),
                'statusStats'  => $this->buildStatusStats($allowedGroups, $agg),
                'pendingTasks' => $this->buildPendingTasks($allowedGroups, $agg),
                'charts'       => $this->buildCharts($allowedGroups, $agg),
                'recent'       => $this->buildRecent($allowedGroups),
                'activity'     => ActivityLog::with('user')->latest()->take(10)->get(),
                'summary'      => $isSuperadmin ? $statistik->summary() : null,
                'mapReports'   => (new \App\Http\Controllers\PetaLaporanController)->reports(
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                    $allowedGroups,
                ),
                // Counters ringan namun TETAP ditaruh di dalam cache agar tidak
                // dieksekusi ulang setiap request saat cache dashboard masih hit.
                // Nilainya tidak dependen per-user, jadi aman digabung ke cache yang sama.
                'activeUsers'  => $isSuperadmin ? User::where('is_active', true)->count() : null,
                'visits'       => $isSuperadmin && class_exists(WebsiteVisit::class) ? WebsiteVisit::count() : null,
            ];
        });

        $recent = $cached['recent'];

        return view('admin.dashboard', [
            'groups'        => AdminRegistry::forUser($user),
            'cards'         => $cached['cards'],
            'recent'        => $recent,
            'activeUsers'   => $cached['activeUsers'],
            'visits'        => $cached['visits'],
            'allowedGroups' => $allowedGroups,
            'charts'        => $cached['charts'],
            'mapReports'    => $cached['mapReports'],
            'activityFeed'  => $cached['activity'],
            // ── Data ringkas tambahan ──
            'summary'       => $cached['summary'],
            'statusStats'   => $cached['statusStats'],
            'pendingTasks'  => $cached['pendingTasks'],
        ]);
    }

    protected function buildCards(array $allowedGroups, User $user, array $agg): array
    {
        $cards = [];
        $bidangTotal = fn (string $bidang) => array_sum($agg['byStatus'][$bidang] ?? []);

        if (in_array('pengendalian', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Pengendalian', 'value' => $bidangTotal('pengendalian'), 'tone' => 'emerald', 'icon' => 'alert-circle'];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Sampah', 'value' => $bidangTotal('sampah-lb3'), 'tone' => 'sky', 'icon' => 'recycle'];
            $cards[] = ['label' => 'Registrasi LB3', 'value' => RegistrasiUsahaLb3::count(), 'tone' => 'amber', 'icon' => 'building'];
        }
        if (in_array('rth', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan RTH', 'value' => $bidangTotal('rth'), 'tone' => 'teal', 'icon' => 'tree'];
            $cards[] = ['label' => 'Permohonan Rekomendasi', 'value' => PermohonanRekomendasi::count(), 'tone' => 'indigo', 'icon' => 'file-text'];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Tata Penataan', 'value' => \App\Models\PengaduanTataPenataan::count(), 'tone' => 'purple', 'icon' => 'building'];
        }
        if (in_array('konten', $allowedGroups)) {
            $cards[] = ['label' => 'Artikel', 'value' => Artikel::count(), 'tone' => 'rose', 'icon' => 'file-text'];
        }

        // Kartu ke-8 (selalu tersedia untuk superadmin/Kepala) — melengkapi grid 4x2.
        if ($user->isSuperadmin()) {
            $cards[] = ['label' => 'Pengguna', 'value' => User::count(), 'tone' => 'bay', 'icon' => 'users'];
        }

        return $cards;
    }

    /**
     * Ringkasan status pengaduan untuk kartu KPI + doughnut performa.
     */
    protected function buildStatusStats(array $allowedGroups, array $agg): array
    {
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        $total = 0; $belum = 0; $proses = 0; $selesai = 0; $ditolak = 0;
        foreach ($bidangValues as $b) {
            $row = $agg['byStatus'][$b] ?? [];
            $total += array_sum($row);
            $belum += (int) ($row['Belum Ditindaklanjuti'] ?? 0) + (int) ($row['Belum Ditinjau'] ?? 0);
            $proses += (int) ($row['Ditindaklanjuti'] ?? 0) + (int) ($row['Ditinjau'] ?? 0);
            $selesai += (int) ($row['Selesai'] ?? 0);
            $ditolak += (int) ($row['Ditolak'] ?? 0);
        }

        $selesaiPct = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return [
            'total'   => $total,
            'belum'   => $belum,
            'proses'  => $proses,
            'selesai' => $selesai,
            'ditolak' => $ditolak,
            'selesai_pct' => $selesaiPct,
            'distribution' => [
                'labels' => ['Belum Ditindaklanjuti', 'Ditindaklanjuti', 'Selesai', 'Ditolak'],
                'data'   => [$belum, $proses, $selesai, $ditolak],
            ],
        ];
    }

    /**
     * Tugas tertunda (butuh tindakan admin) untuk CTA + badges.
     */
    protected function buildPendingTasks(array $allowedGroups, array $agg): array
    {
        $tasks = [];

        if (in_array('pengendalian', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Pengendalian belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'pengendalian', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-pengendalian'])];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Sampah belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'sampah-lb3', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-sampah'])];
            $tasks[] = ['label' => 'Registrasi LB3 menunggu verifikasi', 'count' => RegistrasiUsahaLb3::where('status', 'Diajukan')->count(), 'href' => route('admin.resources.index', ['resource' => 'registrasi-usaha-lb3'])];
        }
        if (in_array('rth', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan RTH belum ditinjau', 'count' => $this->aggCount($agg, 'rth', ['Belum Ditinjau']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-rth'])];
            $tasks[] = ['label' => 'Permohonan rekomendasi belum ditindaklanjuti', 'count' => PermohonanRekomendasi::where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'permohonan-rekomendasi'])];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Tata Penataan belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'tata-penataan', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-tata-penataan'])];
        }

        $total = collect($tasks)->sum('count');

        return ['items' => $tasks, 'total' => $total];
    }

    protected function buildRecent(array $allowedGroups): array
    {
        $recent = [];

        if (array_intersect($allowedGroups, ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'])) {
            $recent['laporan'] = Laporan::latest()->take(5)->get();
        }
        if (in_array('rth', $allowedGroups)) {
            $recent['permohonan'] = PermohonanRekomendasi::latest()->take(5)->get();
            $recent['pinjam_taman'] = \App\Models\PermohonanPinjamTaman::latest()->take(5)->get();
            $recent['tanam_pohon'] = \App\Models\DataTanamPohon::latest()->take(5)->get();
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $recent['registrasi_lb3'] = RegistrasiUsahaLb3::latest()->take(5)->get();
            $recent['rintek_pertek'] = \App\Models\PengajuanRintekPertek::latest()->take(5)->get();
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $recent['tata_penataan'] = \App\Models\PengaduanTataPenataan::latest()->take(5)->get();
        }
        if (in_array('konten', $allowedGroups)) {
            $recent['artikel'] = \App\Models\Artikel::latest()->take(5)->get();
        }

        return $recent;
    }

    /**
     * Data agregat untuk chart (line tren, bar per modul, doughnut status, performa).
     * Semua dihitung dari $agg (2 query) — tanpa query per bulan/per bidang.
     */
    protected function buildCharts(array $allowedGroups, array $agg): array
    {
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        // ── Tren 6 bulan terakhir (line) dari agregat ──
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $trendLabels = $months->map(fn ($m) => $m->translatedFormat('M Y'))->all();

        $trendData = $months->map(function ($m) use ($agg, $bidangValues) {
            $key = $m->format('Y-m');
            $total = 0;
            foreach ($bidangValues as $b) {
                $total += $agg['byMonth'][$b][$key] ?? 0;
            }

            return $total;
        })->all();

        $trendPerBidang = ['labels' => $trendLabels, 'datasets' => []];
        foreach ($bidangValues as $b) {
            $trendPerBidang['datasets'][$b] = $months->map(
                fn ($m) => $agg['byMonth'][$b][$m->format('Y-m')] ?? 0
            )->all();
        }

        // ── Jumlah data per modul (bar) dari kartu ──
        $cards = $this->buildCards($allowedGroups, auth()->user(), $agg);
        $barLabels = [];
        $barData = [];
        foreach ($cards as $card) {
            $barLabels[] = $card['label'];
            $barData[] = $card['value'];
        }

        // ── Distribusi status pengaduan (doughnut) dari agregat ──
        $statusTotals = [];
        foreach ($bidangValues as $b) {
            foreach ($agg['byStatus'][$b] ?? [] as $status => $count) {
                $statusTotals[$status] = ($statusTotals[$status] ?? 0) + $count;
            }
        }
        $statusRows = collect($statusTotals)->filter(fn ($v) => $v > 0);

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
            'performance' => $statusRows->map(function ($total, $status) {
                return [
                    'status' => $status ?: 'Tanpa Status',
                    'total'  => $total,
                ];
            })->values()->all(),
            'trendPerBidang' => $trendPerBidang,
        ];
    }

    /**
     * Hitung agregat Laporan SEKALI: byStatus (bidang→status) dan byMonth (bidang→YYYY-MM).
     * Hanya 2 query grouped, bukan puluhan COUNT terpisah.
     */
    protected function aggregateLaporan(array $bidangValues): array
    {
        $base = Laporan::query();
        if (! empty($bidangValues)) {
            $base->whereIn('bidang', $bidangValues);
        }

        $byStatus = [];
        $statusRows = (clone $base)
            ->select('bidang', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('bidang', 'status')
            ->get();
        foreach ($statusRows as $r) {
            $byStatus[$r->bidang][$r->status] = (int) $r->total;
        }

        $byMonth = [];
        $monthRows = (clone $base)
            ->select('bidang', DB::raw("to_char(created_at, 'YYYY-MM') as ym"), DB::raw('COUNT(*) as total'))
            ->groupBy('bidang', 'ym')
            ->get();
        foreach ($monthRows as $r) {
            $byMonth[$r->bidang][$r->ym] = (int) $r->total;
        }

        return ['byStatus' => $byStatus, 'byMonth' => $byMonth];
    }

    protected function aggCount(array $agg, string $bidang, array $statuses): int
    {
        $row = $agg['byStatus'][$bidang] ?? [];
        $sum = 0;
        foreach ($statuses as $s) {
            $sum += (int) ($row[$s] ?? 0);
        }

        return $sum;
    }
}
