<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sidak;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KalenderSidakController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        // Ambil semua sidak (jadwal + sudah dilaksanakan)
        $sidaks = Sidak::with('objekPengawasan', 'petugas')
            ->whereBetween('tanggal_sidak', [$startDate, $endDate])
            ->orderBy('tanggal_sidak')
            ->get();

        // Format data untuk kalender
        $events = $sidaks->map(function ($sidak) {
            $statusColor = match($sidak->status_tindak_lanjut->value) {
                'belum' => '#f59e0b', // kuning - belum ditindaklanjuti
                'sedang_proses' => '#3b82f6', // biru - sedang proses
                'selesai' => '#10b981', // hijau - selesai
                default => '#6b7280', // abu-abu
            };

            return [
                'id' => $sidak->id,
                'title' => $sidak->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui',
                'start' => $sidak->tanggal_sidak->format('Y-m-d'),
                'color' => $statusColor,
                'extendedProps' => [
                    'petugas' => $sidak->nama_petugas,
                    'hasil' => $sidak->hasil_label ?? $sidak->hasil ?? '-',
                    'status' => $sidak->status_tindak_lanjut?->label() ?? '-',
                    'is_jadwal' => $sidak->is_jadwal,
                    'url' => route('admin.resources.show', ['sidak', $sidak->id]),
                ],
            ];
        });

        // Statistik bulan ini
        $statistik = [
            'total' => $sidaks->count(),
            'jadwal' => $sidaks->where('is_jadwal', true)->count(),
            'terlaksana' => $sidaks->where('is_jadwal', false)->count(),
            'belum' => $sidaks->where('status_tindak_lanjut.value', 'belum')->count(),
            'selesai' => $sidaks->where('status_tindak_lanjut.value', 'selesai')->count(),
        ];

        return view('admin.kalender-sidak.index', compact('events', 'bulan', 'statistik'));
    }
}
