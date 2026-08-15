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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $allowedGroups = $user->allowedGroups();
        $statistik = app(StatistikService::class);

        $cards = $this->buildCards($allowedGroups, $user);
        $recent = $this->buildRecent($allowedGroups);

        $isSuperadmin = $user->isSuperadmin();

        return view('admin.dashboard', [
            'groups'        => AdminRegistry::forUser($user),
            'cards'         => $cards,
            'recent'        => $recent,
            'activeUsers'   => $isSuperadmin ? User::where('is_active', true)->count() : null,
            'visits'        => $isSuperadmin && class_exists(WebsiteVisit::class) ? WebsiteVisit::count() : null,
            'allowedGroups' => $allowedGroups,
            'charts'        => $this->buildCharts($allowedGroups),
            'mapReports'    => (new \App\Http\Controllers\PetaLaporanController)->reports(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
                $allowedGroups,
            ),
            'activityFeed'  => ActivityLog::with('user')->latest()->take(10)->get(),
            // ── Data ringkas tambahan ──
            'summary'       => $isSuperadmin ? $statistik->summary() : null,
            'statusStats'   => $this->buildStatusStats($allowedGroups),
            'pendingTasks'  => $this->buildPendingTasks($allowedGroups, $isSuperadmin),
        ]);
    }

    protected function buildCards(array $allowedGroups, User $user): array
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

        // Kartu ke-8 (selalu tersedia untuk superadmin/Kepala) — melengkapi grid 4x2.
        if ($user->isSuperadmin()) {
            $cards[] = ['label' => 'Pengguna', 'value' => User::count(), 'tone' => 'bay', 'icon' => 'users'];
        }

        return $cards;
    }

    /**
     * Ringkasan status pengaduan untuk kartu KPI + doughnut performa.
     */
    protected function buildStatusStats(array $allowedGroups): array
    {
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        $query = Laporan::query();
        if (! empty($bidangValues)) {
            $query->whereIn('bidang', $bidangValues);
        }

        $total = (clone $query)->count();

        $belum = (clone $query)->whereIn('status', ['Belum Ditindaklanjuti', 'Belum Ditinjau'])->count();
        $proses = (clone $query)->whereIn('status', ['Ditindaklanjuti', 'Ditinjau'])->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();
        $ditolak = (clone $query)->where('status', 'Ditolak')->count();

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
    protected function buildPendingTasks(array $allowedGroups, bool $isSuperadmin): array
    {
        $tasks = [];

        if (in_array('pengendalian', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Pengendalian belum ditindaklanjuti', 'count' => Laporan::where('bidang', 'pengendalian')->where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-pengendalian'])];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Sampah belum ditindaklanjuti', 'count' => Laporan::where('bidang', 'sampah-lb3')->where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-sampah'])];
            $tasks[] = ['label' => 'Registrasi LB3 menunggu verifikasi', 'count' => RegistrasiUsahaLb3::where('status', 'Diajukan')->count(), 'href' => route('admin.resources.index', ['resource' => 'registrasi-usaha-lb3'])];
        }
        if (in_array('rth', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan RTH belum ditinjau', 'count' => Laporan::where('bidang', 'rth')->where('status', 'Belum Ditinjau')->count(), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-rth'])];
            $tasks[] = ['label' => 'Permohonan rekomendasi belum ditindaklanjuti', 'count' => PermohonanRekomendasi::where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'permohonan-rekomendasi'])];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Tata Penataan belum ditindaklanjuti', 'count' => \App\Models\PengaduanTataPenataan::where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-tata-penataan'])];
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
        $cards = $this->buildCards($allowedGroups, auth()->user());
        foreach ($cards as $card) {
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
            'performance' => $statusRows->map(function ($total, $status) {
                return [
                    'status' => $status ?: 'Tanpa Status',
                    'total'  => $total,
                ];
            })->values()->all(),
            'trendPerBidang' => app(StatistikService::class)->trenPerBidang($allowedGroups),
        ];
    }
}
