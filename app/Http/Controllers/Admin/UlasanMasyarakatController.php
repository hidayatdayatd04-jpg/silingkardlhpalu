<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
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

        // Average rating per bidang
        $ratingPerBidang = TicketFeedback::query()
            ->select(
                'feedbackable_type',
                DB::raw('ROUND(AVG(rating), 1) as avg_rating'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('feedbackable_type')
            ->get()
            ->mapWithKeys(fn ($row) => [
                class_basename($row->feedbackable_type) => [
                    'avg_rating' => $row->avg_rating,
                    'total' => $row->total,
                    'label' => $this->modelLabel($row->feedbackable_type),
                    'bidang' => $this->modelBidang($row->feedbackable_type),
                ],
            ]);

        // Filter by user's allowed groups if not superadmin
        if (! $user->isSuperadmin()) {
            $allowedGroups = $user->allowedGroups();
            $ratingPerBidang = $ratingPerBidang->filter(fn ($item) => in_array($item['bidang'], $allowedGroups));
        }

        // Recent feedback with comments
        $recentFeedback = TicketFeedback::query()
            ->whereNotNull('komentar')
            ->where('komentar', '!=', '')
            ->with('feedbackable')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($fb) => [
                'rating' => $fb->rating,
                'komentar' => $fb->komentar,
                'ticket_number' => $fb->feedbackable->nomor_tiket ?? $fb->feedbackable->nomor_pengajuan ?? $fb->feedbackable->nomor_registrasi ?? '#'.$fb->feedbackable_id,
                'model_label' => $this->modelLabel($fb->feedbackable_type),
                'created_at' => $fb->created_at,
            ]);

        return view('admin.ulasan-masyarakat.index', [
            'ratingPerBidang' => $ratingPerBidang,
            'recentFeedback' => $recentFeedback,
            'totalFeedback' => TicketFeedback::count(),
            'avgOverall' => TicketFeedback::avg('rating'),
        ]);
    }

    private function modelLabel(string $class): string
    {
        return match ($class) {
            Laporan::class => 'Pengaduan Masyarakat',
            PengaduanTataPenataan::class => 'Pengaduan Tata Penataan',
            PermohonanRekomendasi::class => 'Permohonan Rekomendasi',
            PengajuanRintekPertek::class => 'RINTEK/PERTEK',
            PermohonanPinjamTaman::class => 'Penyewaan Taman',
            RegistrasiUsahaLb3::class => 'Registrasi LB3',
            default => class_basename($class),
        };
    }

    private function modelBidang(string $class): string
    {
        return match ($class) {
            Laporan::class => 'pengendalian',
            PengaduanTataPenataan::class => 'tata-penataan',
            PermohonanRekomendasi::class => 'pengendalian',
            PengajuanRintekPertek::class => 'sampah-lb3',
            PermohonanPinjamTaman::class => 'rth',
            RegistrasiUsahaLb3::class => 'sampah-lb3',
            default => 'konten',
        };
    }
}
