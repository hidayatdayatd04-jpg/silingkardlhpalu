<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Artikel;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
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
        /** @var User $user */
        $user = auth()->user();
        $allowedGroups = $user->accessibleGroups();
        $statistik = app(StatistikService::class);
        $isSuperadmin = $user->isSuperadmin();

        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        // Versi baru membuat dashboard langsung mengambil angka kunjungan yang
        // sudah di-reset, tanpa menunggu cache dashboard versi lama kedaluwarsa.
        $cacheKey = 'dashboard:v4:' . $user->id . ':' . md5(implode(',', $allowedGroups));
        $cached = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $allowedGroups, $isSuperadmin, $statistik, $bidangValues) {
            $agg = $this->aggregateLaporan($bidangValues);

            return [
                'cards'        => $this->buildCards($allowedGroups, $user, $agg),
                'statusStats'  => $this->buildStatusStats($allowedGroups, $agg),
                'pendingTasks' => $this->buildPendingTasks($allowedGroups, $agg),
                'charts'       => $this->buildCharts($allowedGroups, $agg, $bidangValues),
                'recent'       => $this->buildRecent($allowedGroups),
                'activity'     => $isSuperadmin
                    ? ActivityLog::with('user')->latest()->take(10)->get()
                    : ActivityLog::with('user')->latest()
                        ->where(function ($q) use ($allowedGroups, $user) {
                            $q->whereIn('module', AdminRegistry::allowedNotificationModules($allowedGroups))
                                ->orWhere('user_id', $user->id);
                        })
                        ->take(10)->get(),
                'summary'      => $isSuperadmin ? $statistik->summary() : null,
                'mapReports'   => (new \App\Http\Controllers\PetaLaporanController)->reports(
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                    $allowedGroups,
                ),
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
            $cards[] = ['label' => 'Laporan Pengendalian', 'value' => $bidangTotal('pengendalian'), 'tone' => 'emerald', 'icon' => 'alert-circle', 'slug' => 'pengaduan-pengendalian'];
            $cards[] = ['label' => 'Permohonan Rekomendasi', 'value' => PermohonanRekomendasi::count(), 'tone' => 'indigo', 'icon' => 'file-text', 'slug' => 'permohonan-rekomendasi'];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Sampah', 'value' => $bidangTotal('sampah-lb3'), 'tone' => 'sky', 'icon' => 'recycle', 'slug' => 'pengaduan-sampah'];
            $cards[] = ['label' => 'Registrasi LB3', 'value' => RegistrasiUsahaLb3::count(), 'tone' => 'amber', 'icon' => 'building', 'slug' => 'registrasi-usaha-lb3'];
        }
        if (in_array('rth', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan RTH', 'value' => $bidangTotal('rth'), 'tone' => 'teal', 'icon' => 'tree', 'slug' => 'pengaduan-rth'];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $cards[] = ['label' => 'Laporan Tata Penataan', 'value' => $bidangTotal('tata-penataan'), 'tone' => 'purple', 'icon' => 'building', 'slug' => 'pengaduan-tata-penataan'];
        }
        if (in_array('konten', $allowedGroups)) {
            $cards[] = ['label' => 'Artikel', 'value' => Artikel::count(), 'tone' => 'rose', 'icon' => 'file-text', 'slug' => 'artikel'];
        }

        if ($user->isSuperadmin()) {
            $cards[] = ['label' => 'Pengguna', 'value' => User::count(), 'tone' => 'bay', 'icon' => 'users', 'slug' => 'user'];
        }

        return $cards;
    }

    /**
     * Ringkasan status pengaduan untuk KPI + performa penanganan.
     */
    protected function buildStatusStats(array $allowedGroups, array $agg): array
    {
        $bidangValues = array_values(array_intersect(
            ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'],
            $allowedGroups,
        ));

        $total = 0; $belum = 0; $selesai = 0;
        foreach ($bidangValues as $b) {
            $row = $agg['byStatus'][$b] ?? [];
            $total += array_sum($row);
            $belum += (int) ($row['Belum Ditindaklanjuti'] ?? 0);
            $selesai += (int) ($row['Ditindaklanjuti'] ?? 0);
        }

        $selesaiPct = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return [
            'total'       => $total,
            'belum'       => $belum,
            'proses'      => 0, // reserved for backward-compatibility
            'selesai'     => $selesai,
            'ditolak'     => 0,
            'selesai_pct' => $selesaiPct,
            'distribution' => [
                'labels' => ['Belum Ditindaklanjuti', 'Ditindaklanjuti'],
                'data'   => [$belum, $selesai],
            ],
        ];
    }

    /**
     * Tugas tertunda (butuh tindakan admin).
     */
    protected function buildPendingTasks(array $allowedGroups, array $agg): array
    {
        $tasks = [];

        if (in_array('pengendalian', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Pengendalian belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'pengendalian', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-pengendalian']), 'bidang' => 'Pengendalian', 'color' => '#ef4444'];
            $tasks[] = ['label' => 'Permohonan rekomendasi belum ditindaklanjuti', 'count' => PermohonanRekomendasi::where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'permohonan-rekomendasi']), 'bidang' => 'Pengendalian', 'color' => '#ef4444'];
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Sampah belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'sampah-lb3', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-sampah']), 'bidang' => 'Sampah & LB3', 'color' => '#0284c7'];
            $tasks[] = ['label' => 'Registrasi LB3 belum ditindaklanjuti', 'count' => RegistrasiUsahaLb3::where('status', 'Belum Ditindaklanjuti')->count(), 'href' => route('admin.resources.index', ['resource' => 'registrasi-usaha-lb3']), 'bidang' => 'Sampah & LB3', 'color' => '#0284c7'];
        }
        if (in_array('rth', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan RTH belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'rth', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-rth']), 'bidang' => 'RTH', 'color' => '#10b981'];
        }
        if (in_array('tata-penataan', $allowedGroups)) {
            $tasks[] = ['label' => 'Laporan Tata Penataan belum ditindaklanjuti', 'count' => $this->aggCount($agg, 'tata-penataan', ['Belum Ditindaklanjuti']), 'href' => route('admin.resources.index', ['resource' => 'pengaduan-tata-penataan']), 'bidang' => 'Tata Penataan', 'color' => '#8b5cf6'];
        }

        $total = collect($tasks)->sum('count');

        return ['items' => $tasks, 'total' => $total];
    }

    protected function buildRecent(array $allowedGroups): array
    {
        $recent = [];

        if (array_intersect($allowedGroups, ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan'])) {
            $labels = [
                'pengendalian' => 'Pengendalian',
                'sampah-lb3' => 'Sampah & LB3',
                'rth' => 'RTH',
                'tata-penataan' => 'Tata Penataan',
            ];
            $sources = [
                'pengendalian' => PengaduanPengendalian::class,
                'sampah-lb3' => PengaduanSampah::class,
                'rth' => PengaduanRth::class,
                'tata-penataan' => PengaduanTataPenataan::class,
            ];
            $slugs = [
                'pengendalian' => 'pengaduan-pengendalian',
                'sampah-lb3' => 'pengaduan-sampah',
                'rth' => 'pengaduan-rth',
                'tata-penataan' => 'pengaduan-tata-penataan',
            ];

            $recent['laporan'] = collect($sources)
                ->filter(fn ($class, $bidang) => in_array($bidang, $allowedGroups, true))
                ->flatMap(function ($class, $bidang) use ($labels, $slugs) {
                    return $class::latest()->take(6)->get()->map(function ($item) use ($labels, $slugs, $bidang) {
                        $item->bidang_label = $labels[$bidang];
                        $item->bidang_key = $bidang;
                        $item->status_text = $item->status instanceof \BackedEnum ? $item->status->label() : (string) $item->status;
                        $item->detail_url = route('admin.resources.edit', ['resource' => $slugs[$bidang], 'record' => $item->id]);

                        return $item;
                    });
                })
                ->sortByDesc('created_at')
                ->take(6)
                ->values();
        }
        if (in_array('pengendalian', $allowedGroups)) {
            $recent['permohonan'] = PermohonanRekomendasi::latest()->take(5)->get();
        }
        if (in_array('rth', $allowedGroups)) {
            $recent['pinjam_taman'] = \App\Models\PermohonanPinjamTaman::latest()->take(5)->get();
            $recent['tanam_pohon'] = \App\Models\DataTanamPohon::latest()->take(5)->get();
        }
        if (in_array('sampah-lb3', $allowedGroups)) {
            $recent['registrasi_lb3'] = RegistrasiUsahaLb3::latest()->take(5)->get();
            $recent['rintek_pertek'] = \App\Models\PengajuanRintekPertek::latest()->take(5)->get();
        }
        if (in_array('konten', $allowedGroups)) {
            $recent['artikel'] = \App\Models\Artikel::latest()->take(5)->get();
        }

        return $recent;
    }

    /**
     * Data agregat untuk chart.
     */
    protected function buildCharts(array $allowedGroups, array $agg, array $bidangValues): array
    {
        // ── Tren 6 bulan terakhir (line) ──
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

        $bidangDisplayNames = [
            'pengendalian' => 'Pengendalian',
            'sampah-lb3' => 'Sampah & LB3',
            'rth' => 'RTH',
            'tata-penataan' => 'Tata Penataan',
        ];

        $trendPerBidang = ['labels' => $trendLabels, 'datasets' => []];
        foreach ($bidangValues as $b) {
            $trendPerBidang['datasets'][$b] = [
                'label' => $bidangDisplayNames[$b] ?? ucfirst($b),
                'data' => $months->map(
                    fn ($m) => $agg['byMonth'][$b][$m->format('Y-m')] ?? 0
                )->all(),
            ];
        }

        // ── Jumlah data per modul (bar) ──
        $cards = $this->buildCards($allowedGroups, auth()->user(), $agg);
        $barLabels = [];
        $barData = [];
        foreach ($cards as $card) {
            $barLabels[] = $card['label'];
            $barData[] = $card['value'];
        }

        // ── Distribusi status ──
        $statusTotals = [];
        foreach ($bidangValues as $b) {
            foreach ($agg['byStatus'][$b] ?? [] as $status => $count) {
                $statusTotals[$status] = ($statusTotals[$status] ?? 0) + $count;
            }
        }
        $statusRows = collect($statusTotals)->filter(fn ($v) => $v > 0);

        // ── Distribusi Pengaduan per Bidang ──
        $bidangDistLabels = [];
        $bidangDistData = [];
        foreach ($bidangValues as $b) {
            $c = array_sum($agg['byStatus'][$b] ?? []);
            if ($c > 0) {
                $bidangDistLabels[] = $bidangDisplayNames[$b] ?? ucfirst($b);
                $bidangDistData[] = $c;
            }
        }

        // ── Top Kategori Pengaduan ──
        $topCategories = $this->getTopCategories($bidangValues);

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
            'bidangDistribution' => [
                'labels' => $bidangDistLabels,
                'data'   => $bidangDistData,
            ],
            'topCategories' => $topCategories,
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
     * Top Kategori Pengaduan across all accessible models
     */
    protected function getTopCategories(array $bidangValues): array
    {
        $sources = [
            'pengendalian' => PengaduanPengendalian::class,
            'sampah-lb3' => PengaduanSampah::class,
            'rth' => PengaduanRth::class,
            'tata-penataan' => PengaduanTataPenataan::class,
        ];

        $counts = [];
        foreach ($bidangValues as $b) {
            $model = $sources[$b] ?? null;
            if (! $model) continue;
            $rows = $model::query()
                ->select('jenis_pengaduan', DB::raw('COUNT(*) as total'))
                ->whereNotNull('jenis_pengaduan')
                ->groupBy('jenis_pengaduan')
                ->get();
            foreach ($rows as $r) {
                $cat = trim((string) $r->jenis_pengaduan);
                if ($cat !== '') {
                    $counts[$cat] = ($counts[$cat] ?? 0) + (int) $r->total;
                }
            }
        }
        arsort($counts);
        $top = array_slice($counts, 0, 5, true);

        return [
            'labels' => array_keys($top),
            'data'   => array_values($top),
        ];
    }

    /**
     * Hitung agregat pengaduan SEKALI per tabel bidang: byStatus dan byMonth.
     */
    protected function aggregateLaporan(array $bidangValues): array
    {
        $sources = [
            'pengendalian' => PengaduanPengendalian::class,
            'sampah-lb3' => PengaduanSampah::class,
            'rth' => PengaduanRth::class,
            'tata-penataan' => PengaduanTataPenataan::class,
        ];

        $byStatus = [];
        $byMonth = [];

        foreach ($bidangValues as $bidang) {
            $modelClass = $sources[$bidang] ?? null;

            if (! $modelClass) {
                continue;
            }

            $statusRows = $modelClass::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get();
            foreach ($statusRows as $r) {
                $status = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;
                $byStatus[$bidang][$status] = (int) $r->total;
            }

            $monthRows = $modelClass::query()
                ->select(DB::raw("to_char(created_at, 'YYYY-MM') as ym"), DB::raw('COUNT(*) as total'))
                ->groupBy('ym')
                ->get();
            foreach ($monthRows as $r) {
                $byMonth[$bidang][$r->ym] = (int) $r->total;
            }
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
