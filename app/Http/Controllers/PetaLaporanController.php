<?php

namespace App\Http\Controllers;

use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\JenisPengaduanTataPenataan;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PetaLaporanController extends Controller
{
    /**
     * Warna marker per bidang (4 bidang dinas).
     */
    private const BIDANG_COLORS = [
        'pengendalian'  => '#ef4444',
        'sampah-lb3'    => '#0284c7',
        'rth'           => '#10b981',
        'tata-penataan' => '#8b5cf6',
    ];

    /**
     * Label bidang untuk tampilan popup/legend/list.
     */
    private const BIDANG_LABELS = [
        'pengendalian'  => 'Pengendalian',
        'sampah-lb3'    => 'Sampah & LB3',
        'rth'           => 'RTH',
        'tata-penataan' => 'Tata Penataan',
    ];

    /**
     * Resource slugs per bidang.
     */
    private const BIDANG_SLUGS = [
        'pengendalian'  => 'pengaduan-pengendalian',
        'sampah-lb3'    => 'pengaduan-sampah',
        'rth'           => 'pengaduan-rth',
        'tata-penataan' => 'pengaduan-tata-penataan',
    ];

    /**
     * Endpoint data JSON untuk dashboard admin — memfilter laporan berdasarkan
     * bidang yang boleh diakses user yang sedang login.
     */
    public function data(): JsonResponse
    {
        $from = request()->input('from', now()->startOfMonth()->toDateString());
        $to = request()->input('to', now()->endOfMonth()->toDateString());
        $bidang = request()->input('bidang');
        $status = request()->input('status');
        $search = request()->input('search');

        /** @var User|null $user */
        $user = Auth::user();
        $allowedGroups = $user?->accessibleGroups() ?? [];

        if ($bidang && in_array($bidang, $allowedGroups, true)) {
            $allowedGroups = [$bidang];
        }

        return response()->json([
            'reports' => $this->query($from, $to, $allowedGroups, $status, $search),
        ]);
    }

    /**
     * Ambil laporan untuk ditampilkan langsung di dashboard (render awal).
     */
    public function reports(string $from, string $to, array $allowedGroups = []): array
    {
        return $this->query($from, $to, $allowedGroups);
    }

    /**
     * Kumpulkan semua pengaduan berkoordinat dari keempat tabel per bidang
     * dalam rentang tanggal, dibatasi pada bidang yang diizinkan (`$allowedGroups`).
     */
    private function query(string $from, string $to, array $allowedGroups = [], ?string $statusFilter = null, ?string $search = null): array
    {
        $items = Collection::make();

        $sources = [
            Bidang::PENGENDALIAN->value  => [PengaduanPengendalian::class, 'P', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'pengendalian', JenisPengaduanPengendalian::options(), $idPrefix)],
            Bidang::SAMPAH_LB3->value    => [PengaduanSampah::class, 'S', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'sampah-lb3', JenisPengaduanSampah::options(), $idPrefix)],
            Bidang::RTH->value           => [PengaduanRth::class, 'R', fn ($l, $idPrefix) => $this->fromPengaduan($l, 'rth', JenisPengaduanRth::options(), $idPrefix)],
            Bidang::TATA_PENATAAN->value => [PengaduanTataPenataan::class, 'T', fn ($l, $idPrefix) => $this->fromTtp($l, $idPrefix)],
        ];

        foreach ($sources as $group => [$modelClass, $idPrefix, $mapper]) {
            if (! in_array($group, $allowedGroups, true)) {
                continue;
            }

            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            $query = $modelClass::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($from && $from !== 'all') {
                $query->whereDate('created_at', '>=', $from);
            }
            if ($to && $to !== 'all') {
                $query->whereDate('created_at', '<=', $to);
            }

            if ($statusFilter && $statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            if ($search && trim($search) !== '') {
                $s = trim($search);
                $query->where(function ($q) use ($s) {
                    $q->where('nomor_tiket', 'ilike', "%{$s}%")
                      ->orWhere('alamat', 'ilike', "%{$s}%")
                      ->orWhere('deskripsi', 'ilike', "%{$s}%")
                      ->orWhere('nama_pelapor', 'ilike', "%{$s}%");
                });
            }

            $results = $query->with('fotos')->orderBy('created_at', 'desc')->get();

            $items = $items->concat(
                $results
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
     * Format nomor WhatsApp untuk link langsung
     */
    private function formatWaLink(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (! str_starts_with($clean, '62') && strlen($clean) > 8) {
            $clean = '62' . $clean;
        }

        return strlen($clean) >= 10 ? "https://wa.me/{$clean}" : null;
    }

    /**
     * Normalisasi pengaduan dari tabel pengendalian, sampah, rth.
     */
    private function fromPengaduan(mixed $l, string $bidang, array $jenisOptions, string $idPrefix = ''): array
    {
        $statusRaw = $l->status instanceof \BackedEnum ? $l->status->value : (string) ($l->status ?? '');
        $statusLabel = $l->status_label ?? ($l->status instanceof \BackedEnum ? $l->status->value : $statusRaw);
        $resourceSlug = self::BIDANG_SLUGS[$bidang] ?? 'pengaduan-' . $bidang;

        $firstFoto = $l->relationLoaded('fotos') ? $l->fotos->first() : null;
        $fotoThumb = $firstFoto?->fullUrl();


        return [
            'id'            => $idPrefix . $l->id,
            'raw_id'        => $l->id,
            'bidang'        => $bidang,
            'bidang_label'  => self::BIDANG_LABELS[$bidang] ?? $bidang,
            'bidang_color'  => self::BIDANG_COLORS[$bidang] ?? '#6b7280',
            'nomor_tiket'   => $l->nomor_tiket,
            'nama_pelapor'  => $l->nama_pelapor ?: 'Masyarakat',
            'nomor_hp'      => $l->nomor_hp ?: '-',
            'wa_url'        => $this->formatWaLink($l->nomor_hp),
            'jenis_pengaduan' => $l->jenis_pengaduan,
            'jenis_label'   => $jenisOptions[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            'alamat'        => $l->alamat ?: 'Alamat tidak diisi',
            'deskripsi'     => $l->deskripsi ?: '',
            'status'        => $statusRaw,
            'status_label'  => $statusLabel,
            'status_color'  => $this->statusHex($statusRaw),
            'tanggal'       => $l->created_at?->translatedFormat('d M Y H:i') ?? '-',
            'latitude'      => (float) $l->latitude,
            'longitude'     => (float) $l->longitude,
            'foto_count'    => $l->relationLoaded('fotos') ? $l->fotos->count() : $l->fotos()->count(),
            'foto_thumb'    => $fotoThumb,
            'resource_slug' => $resourceSlug,
            'detail_url'    => route('admin.resources.edit', ['resource' => $resourceSlug, 'record' => $l->id]),
            'waktu'         => $l->created_at,
        ];
    }

    /**
     * Normalisasi pengaduan Tata Penataan dari tabel `pengaduan_tata_penataan`.
     */
    private function fromTtp(PengaduanTataPenataan $l, string $idPrefix = 'T'): array
    {
        $statusRaw = $l->status instanceof \BackedEnum ? $l->status->value : (string) ($l->status ?? '');
        $statusLabel = $l->status_label ?? ($l->status instanceof \BackedEnum ? $l->status->value : $statusRaw);
        $resourceSlug = self::BIDANG_SLUGS['tata-penataan'];

        $firstFoto = $l->relationLoaded('fotos') ? $l->fotos->first() : null;
        $fotoThumb = $firstFoto?->fullUrl();


        return [
            'id'            => $idPrefix . $l->id,
            'raw_id'        => $l->id,
            'bidang'        => 'tata-penataan',
            'bidang_label'  => self::BIDANG_LABELS['tata-penataan'],
            'bidang_color'  => self::BIDANG_COLORS['tata-penataan'],
            'nomor_tiket'   => $l->nomor_tiket,
            'nama_pelapor'  => $l->nama_pelapor ?: 'Masyarakat',
            'nomor_hp'      => $l->nomor_hp ?: '-',
            'wa_url'        => $this->formatWaLink($l->nomor_hp),
            'jenis_pengaduan' => $l->jenis_pengaduan,
            'jenis_label'   => JenisPengaduanTataPenataan::options()[$l->jenis_pengaduan] ?? $l->jenis_pengaduan,
            'nama_terlapor' => $l->nama_terlapor ?? null,
            'nama_perusahaan_terlapor' => $l->nama_perusahaan_terlapor ?? null,
            'alamat'        => $l->alamat ?: 'Alamat tidak diisi',
            'deskripsi'     => $l->deskripsi ?: '',
            'status'        => $statusRaw,
            'status_label'  => $statusLabel,
            'status_color'  => $this->statusHex($statusRaw),
            'tanggal'       => $l->created_at?->translatedFormat('d M Y H:i') ?? '-',
            'latitude'      => (float) $l->latitude,
            'longitude'     => (float) $l->longitude,
            'foto_count'    => $l->relationLoaded('fotos') ? $l->fotos->count() : $l->fotos()->count(),
            'foto_thumb'    => $fotoThumb,
            'resource_slug' => $resourceSlug,
            'detail_url'    => route('admin.resources.edit', ['resource' => $resourceSlug, 'record' => $l->id]),
            'waktu'         => $l->created_at,
        ];
    }

    /**
     * Map status mentah ke warna hex yang konsisten lintas bidang.
     */
    private function statusHex(string $status): string
    {
        return match ($status) {
            'Ditindaklanjuti' => '#10b981',
            'Belum Ditindaklanjuti' => '#f59e0b',
            default => '#6b7280',
        };
    }
}

