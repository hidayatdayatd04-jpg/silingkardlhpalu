<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sosialisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KalenderSosialisasiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        // Ambil semua sosialisasi
        $sosialisasis = Sosialisasi::with('pesertas.objekPengawasan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        // Format data untuk kalender
        $events = $sosialisasis->map(function ($sosialisasi) {
            return [
                'id' => $sosialisasi->id,
                'title' => $sosialisasi->judul,
                'start' => $sosialisasi->tanggal->format('Y-m-d'),
                'color' => '#8b5cf6', // ungu untuk sosialisasi
                'extendedProps' => [
                    'materi' => Str::limit($sosialisasi->materi, 100),
                    'jumlah_peserta' => $sosialisasi->pesertas->count(),
                    'hasil_evaluasi' => $sosialisasi->hasil_evaluasi,
                    'url' => route('admin.resources.show', ['sosialisasi', $sosialisasi->id]),
                ],
            ];
        });

        // Statistik bulan ini
        $statistik = [
            'total' => $sosialisasis->count(),
            'total_peserta' => $sosialisasis->sum(fn ($s) => $s->pesertas->count()),
            'sudah_evaluasi' => $sosialisasis->where('hasil_evaluasi', '!=', null)->count(),
        ];

        return view('admin.kalender-sosialisasi.index', compact('events', 'bulan', 'statistik'));
    }
}
