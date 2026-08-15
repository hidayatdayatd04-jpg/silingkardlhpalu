<?php

namespace App\Http\Controllers;

use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\StatusPengaduanTataPenataan;
use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PetaLaporanController extends Controller
{
    /**
     * Warna marker per bidang (4 bidang).
     */
    private const BIDANG_COLORS = [
        'pengendalian' => '#ef4444',
        'sampah-lb3'   => '#3b82f6',
        'rth'          => '#22c55e',
        'tata-penataan'=> '#8b5cf6',
    ];

    /**
     * Label bidang untuk tampilan popup/legend.
     */
    private const BIDANG_LABELS = [
        'pengendalian' => 'Pengendalian',
        'sampah-lb3'   => 'Sampah & LB3',
        'rth'          => 'RTH',
        'tata-penataan'=> 'Tata Penataan',
    ];

    /**
     * Endpoint data untuk dashboard admin — memfilter laporan berdasarkan
     * bidang yang boleh diakses user yang sedang login.
     */
    public function data(): JsonResponse
    {
        $from = request()->input('from', now()->startOfMonth()->toDateString());
        $to = request()->input('to', now()->endOfMonth()->toDateString());
        $allowedGroups = auth()->user()?->allowedGroups() ?? [];

        return response()->json([
            'reports' => $this->query($from, $to, $allowedGroups),
        ]);
    }

    /**
     * Ambil laporan untuk ditampilkan langsung di dashboard (render awal).
     * Dapat dipanggil dari DashboardController dengan filter akses yang sama.
     */
    public function reports(string $from, string $to, array $allowedGroups = []): array
    {
        return $this->query($from, $to, $allowedGroups);
    }

    /**
     * Kumpulkan semua laporan berkoordinat dari keempat bidang
     * (Pengendalian, Sampah & LB3, RTH via tabel `laporans`;
     * Tata Penataan via tabel `pengaduan_tata_penataans`) dalam rentang tanggal,
     * dibatasi pada bidang yang diizinkan (`$allowedGroups`).
     */
    private function query(string $from, string $to, array $allowedGroups = []): array
    {
        $laporanBidangs = array_values(array_intersect(
            [Bidang::PENGENDALIAN->value, Bidang::SAMPAH_LB3->value, Bidang::RTH->value],
            $allowedGroups,
        ));

        $laporanItems = Collection::make();
        if (! empty($laporanBidangs)) {
            $laporanItems = Laporan::query()
                ->whereIn('bidang', $laporanBidangs)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(fn (Laporan $l) => ! ((float) $l->latitude === 0.0 && (float) $l->longitude === 0.0))
                ->map(fn (Laporan $l) => $this->fromLaporan($l));
        }

        $ttpItems = Collection::make();
        if (in_array(Bidang::TATA_PENATAAN->value, $allowedGroups, true)) {
            $ttpItems = PengaduanTataPenataan::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(fn (PengaduanTataPenataan $l) => ! ((float) $l->latitude === 0.0 && (float) $l->longitude === 0.0))
                ->map(fn (PengaduanTataPenataan $l) => $this->fromTtp($l));
        }

        return $laporanItems
            ->concat($ttpItems)
            ->sortByDesc('waktu')
            ->map(fn (array $item) => collect($item)->except('waktu')->all())
            ->values()
            ->all();
    }

    /**
     * Normalisasi laporan dari tabel `laporans` (pengendalian / sampah-lb3 / rth).
     * TIDAK menyertakan data pribadi pelapor (nama_pelapor, nomor_hp, email).
     */
    private function fromLaporan(Laporan $l): array
    {
        $bidang = $l->bidang?->value ?? 'pengendalian';
        $statusRaw = (string) $l->status;

        $jenisLabel = match ($bidang) {
            'pengendalian' => JenisPengaduanPengendalian::options()[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            'sampah-lb3'   => JenisPengaduanSampah::options()[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            'rth'          => JenisPengaduanRth::options()[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            default        => $l->jenis_pengaduan,
        };

        return [
            'id' => 'L'.$l->id,
            'bidang' => $bidang,
            'bidang_label' => self::BIDANG_LABELS[$bidang] ?? $bidang,
            'bidang_color' => self::BIDANG_COLORS[$bidang] ?? '#6b7280',
            'nomor_tiket' => $l->nomor_tiket,
            'jenis_label' => $jenisLabel,
            'alamat' => $l->alamat,
            'deskripsi' => $l->deskripsi,
            'status' => $statusRaw,
            'status_label' => $l->status_label,
            'status_color' => $this->statusHex($statusRaw),
            'tanggal' => $l->created_at->format('d M Y H:i'),
            'latitude' => (float) $l->latitude,
            'longitude' => (float) $l->longitude,
            'foto_count' => $l->fotos()->count(),
            'waktu' => $l->created_at,
        ];
    }

    /**
     * Normalisasi pengaduan Tata Penataan dari tabel `pengaduan_tata_penataans`.
     * TIDAK menyertakan data pribadi pelapor (nama_pelapor, no_hp, email).
     */
    private function fromTtp(PengaduanTataPenataan $l): array
    {
        $statusEnum = $l->status;
        $statusRaw = $statusEnum instanceof StatusPengaduanTataPenataan ? $statusEnum->value : (string) $l->status;
        $statusLabel = $statusEnum instanceof StatusPengaduanTataPenataan ? $statusEnum->label() : $statusRaw;

        return [
            'id' => 'T'.$l->id,
            'bidang' => 'tata-penataan',
            'bidang_label' => self::BIDANG_LABELS['tata-penataan'],
            'bidang_color' => self::BIDANG_COLORS['tata-penataan'],
            'nomor_tiket' => $l->nomor_tiket,
            'jenis_label' => JenisPengaduanTataPenataan::options()[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            'alamat' => $l->alamat,
            'deskripsi' => $l->deskripsi,
            'status' => $statusRaw,
            'status_label' => $statusLabel,
            'status_color' => $this->statusHex($statusRaw),
            'tanggal' => $l->created_at->format('d M Y H:i'),
            'latitude' => (float) $l->latitude,
            'longitude' => (float) $l->longitude,
            'foto_count' => $l->fotos()->count(),
            'waktu' => $l->created_at,
        ];
    }

    /**
     * Map status mentah ke warna hex yang konsisten lintas bidang.
     */
    private function statusHex(string $status): string
    {
        return match ($status) {
            'Selesai', 'selesai'                          => '#10b981',
            'Ditindaklanjuti', 'Ditinjau', 'Diproses', 'ditugaskan' => '#f59e0b',
            'Ditolak'                                     => '#ef4444',
            default                                       => '#6b7280',
        };
    }
}
