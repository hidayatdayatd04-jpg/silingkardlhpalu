<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\TicketFeedback;
use Illuminate\Support\Facades\DB;

class UlasanMasyarakatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSuperadmin = $user->isSuperadmin();

        // Tentukan model class yang diizinkan berdasarkan role
        $allowedTypes = $isSuperadmin
            ? null  // null = semua
            : $this->allowedModelTypes($user->allowedGroups());

        // ── Rating per bidang ──────────────────────────────────────────────
        $ratingQuery = TicketFeedback::query()
            ->select(
                'feedbackable_type',
                DB::raw('ROUND(AVG(rating), 1) as avg_rating'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('feedbackable_type');

        if ($allowedTypes !== null) {
            $ratingQuery->whereIn('feedbackable_type', $allowedTypes);
        }

        $ratingPerBidang = $ratingQuery->get()
            ->mapWithKeys(fn ($row) => [
                class_basename($row->feedbackable_type) => [
                    'avg_rating' => $row->avg_rating,
                    'total'      => $row->total,
                    'label'      => $this->modelLabel($row->feedbackable_type),
                    'bidang'     => $this->modelBidang($row->feedbackable_type),
                ],
            ]);

        // ── Komentar terbaru ───────────────────────────────────────────────
        $feedbackQuery = TicketFeedback::query()
            ->whereNotNull('komentar')
            ->where('komentar', '!=', '')
            ->with('feedbackable')
            ->latest()
            ->take(20);

        if ($allowedTypes !== null) {
            $feedbackQuery->whereIn('feedbackable_type', $allowedTypes);
        }

        $recentFeedback = $feedbackQuery->get()
            ->map(fn ($fb) => [
                'rating'       => $fb->rating,
                'komentar'     => $fb->komentar,
                'ticket_number'=> $fb->feedbackable->nomor_tiket
                    ?? $fb->feedbackable->nomor_pengajuan
                    ?? $fb->feedbackable->nomor_registrasi
                    ?? '#'.$fb->feedbackable_id,
                'model_label'  => $this->modelLabel($fb->feedbackable_type),
                'created_at'   => $fb->created_at,
            ]);

        // ── Statistik ringkasan (sesuai akses role) ────────────────────────
        $statsQuery = TicketFeedback::query();
        if ($allowedTypes !== null) {
            $statsQuery->whereIn('feedbackable_type', $allowedTypes);
        }

        return view('admin.ulasan-masyarakat.index', [
            'ratingPerBidang' => $ratingPerBidang,
            'recentFeedback'  => $recentFeedback,
            'totalFeedback'   => (clone $statsQuery)->count(),
            'avgOverall'      => (clone $statsQuery)->avg('rating'),
        ]);
    }

    /**
     * Kembalikan daftar FQCN model yang diizinkan berdasarkan grup role user.
     * Mengembalikan null berarti semua model diizinkan (superadmin).
     */
    private function allowedModelTypes(array $allowedGroups): array
    {
        // Peta grup → model class yang termasuk dalam grup tersebut
        $groupModelMap = [
            'pengendalian'  => [PengaduanPengendalian::class, PermohonanRekomendasi::class],
            'sampah-lb3'    => [PengaduanSampah::class, PengajuanRintekPertek::class, RegistrasiUsahaLb3::class],
            'rth'           => [PengaduanRth::class, PermohonanPinjamTaman::class],
            'tata-penataan' => [PengaduanTataPenataan::class],
        ];

        $types = [];
        foreach ($allowedGroups as $group) {
            if (isset($groupModelMap[$group])) {
                $types = array_merge($types, $groupModelMap[$group]);
            }
        }

        return array_unique($types);
    }

    private function modelLabel(string $class): string
    {
        return match ($class) {
            PengaduanPengendalian::class => 'Pengaduan Pengendalian',
            PengaduanSampah::class       => 'Pengaduan Sampah & LB3',
            PengaduanRth::class          => 'Pengaduan RTH',
            PengaduanTataPenataan::class => 'Pengaduan Tata Penataan',
            PermohonanRekomendasi::class  => 'Permohonan Rekomendasi',
            PengajuanRintekPertek::class  => 'RINTEK/PERTEK',
            PermohonanPinjamTaman::class  => 'Penyewaan Taman',
            RegistrasiUsahaLb3::class    => 'Registrasi LB3',
            default                      => class_basename($class),
        };
    }

    private function modelBidang(string $class): string
    {
        return match ($class) {
            PengaduanPengendalian::class => 'pengendalian',
            PengaduanSampah::class       => 'sampah-lb3',
            PengaduanRth::class          => 'rth',
            PengaduanTataPenataan::class => 'tata-penataan',
            PermohonanRekomendasi::class  => 'pengendalian',
            PengajuanRintekPertek::class  => 'sampah-lb3',
            PermohonanPinjamTaman::class  => 'rth',
            RegistrasiUsahaLb3::class    => 'sampah-lb3',
            default                      => 'konten',
        };
    }
}
