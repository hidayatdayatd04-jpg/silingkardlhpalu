<?php

namespace App\Http\Controllers;

use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\StatusPengaduanTataPenataan;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
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
     * Kumpulkan semua pengaduan berkoordinat dari keempat tabel per bidang
     * (pengaduan_pengendalian, pengaduan_sampah, pengaduan_rth,
     * pengaduan_tata_penataan) dalam rentang tanggal, dibatasi pada bidang
     * yang diizinkan (`$allowedGroups`).
     */
    private function query(string $from, string $to, array $allowedGroups = []): array
    {
        $items = Collection::make();

        $sources = [
            Bidang::PENGENDALIAN->value => [PengaduanPengendalian::class, 'P', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'pengendalian', JenisPengaduanPengendalian::options(), $idPrefix)],
            Bidang::SAMPAH_LB3->value => [PengaduanSampah::class, 'S', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'sampah-lb3', JenisPengaduanSampah::options(), $idPrefix)],
            Bidang::RTH->value => [PengaduanRth::class, 'R', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'rth', JenisPengaduanRth::options(), $idPrefix)],
            Bidang::TATA_PENATAAN->value => [PengaduanTataPenataan::class, 'T', fn ($l, $idPrefix) => $this->fromTtp($l, $idPrefix)],
        ];

        foreach ($sources as $group => [$modelClass, $idPrefix, $mapper]) {
            if (! in_array($group, $allowedGroups, true)) {
                continue;
            }

            $items = $items->concat(
                $modelClass::query()
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereDate('created_at', '>=', $from)
                    ->whereDate('created_at', '<=', $to)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->filter(fn ($l) => ! ((float) $l->latitude === 0.0 && (float) $l->longitude === 0.0))
                    ->map(fn ($l) => $mapper($l, $idPrefix))
            );
        }

        return $items
            ->sortByDesc('waktu')
            ->map(fn (array $item) => collect($item)->except('waktu')->all())
            ->values()
            ->all();
    }

    /**
     * Normalisasi pengaduan dari tabel pengaduan_pengendalian /
     * pengaduan_sampah / pengaduan_rth. TIDAK menyertakan data pribadi
     * pelapor (nama_pelapor, nomor_hp).
     */
    private function fromPengaduan($l, string $bidang, array $jenisOptions, string $idPrefix = ''): array
    {
        $statusRaw = $l->status instanceof \BackedEnum ? $l->status->value : (string) $l->status;

        return [
            'id' => $idPrefix.$l->id,
            'bidang' => $bidang,
            'bidang_label' => self::BIDANG_LABELS[$bidang] ?? $bidang,
            'bidang_color' => self::BIDANG_COLORS[$bidang] ?? '#6b7280',
            'nomor_tiket' => $l->nomor_tiket,
            'jenis_label' => $jenisOptions[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
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
     * Normalisasi pengaduan Tata Penataan dari tabel `pengaduan_tata_penataan`.
     * TIDAK menyertakan data pribadi pelapor (nama_pelapor, nomor_hp).
     */
    private function fromTtp(PengaduanTataPenataan $l, string $idPrefix = 'T'): array
    {
        $statusEnum = $l->status;
        $statusRaw = $statusEnum instanceof StatusPengaduanTataPenataan ? $statusEnum->value : (string) $l->status;
        $statusLabel = $statusEnum instanceof StatusPengaduanTataPenataan ? $statusEnum->label() : $statusRaw;

        return [
            'id' => $idPrefix.$l->id,
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
