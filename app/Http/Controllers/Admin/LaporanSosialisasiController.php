<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LaporanSosialisasiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        // Rekap bulanan
        $rekap = [
            'total_sosialisasi' => Sosialisasi::whereBetween('tanggal', [$startDate, $endDate])->count(),
            'total_peserta' => SosialisasiPeserta::whereHas('sosialisasi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })->count(),
            'sudah_evaluasi' => Sosialisasi::whereBetween('tanggal', [$startDate, $endDate])
                ->whereNotNull('hasil_evaluasi')
                ->count(),
            'belum_evaluasi' => Sosialisasi::whereBetween('tanggal', [$startDate, $endDate])
                ->whereNull('hasil_evaluasi')
                ->count(),
            'sertifikat_terbit' => SosialisasiPeserta::whereHas('sosialisasi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })->whereNotNull('sertifikat_path')->count(),
        ];

        // Tren 6 bulan terakhir
        $tren = collect(range(5, 0))->map(fn ($i) => [
            'bulan' => Carbon::now()->subMonths($i)->translatedFormat('M Y'),
            'sosialisasi' => Sosialisasi::whereMonth('tanggal', Carbon::now()->subMonths($i)->month)
                ->whereYear('tanggal', Carbon::now()->subMonths($i)->year)->count(),
            'peserta' => SosialisasiPeserta::whereHas('sosialisasi', function ($q) use ($i) {
                $q->whereMonth('tanggal', Carbon::now()->subMonths($i)->month)
                    ->whereYear('tanggal', Carbon::now()->subMonths($i)->year);
            })->count(),
        ]);

        // Daftar sosialisasi bulan ini
        $sosialisasis = Sosialisasi::with('pesertas.objekPengawasan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Distribusi peserta perusahaan
        $distribusiPeserta = SosialisasiPeserta::with('objekPengawasan')
            ->whereHas('sosialisasi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->get()
            ->groupBy('objek_pengawasan_id')
            ->map(fn ($items) => [
                'nama' => $items->first()->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui',
                'jumlah' => $items->count(),
            ])
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        return view('admin.laporan-sosialisasi.index', compact(
            'rekap', 'tren', 'sosialisasis', 'distribusiPeserta', 'bulan'
        ));
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $rekap = [
            'total_sosialisasi' => Sosialisasi::whereBetween('tanggal', [$startDate, $endDate])->count(),
            'total_peserta' => SosialisasiPeserta::whereHas('sosialisasi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })->count(),
        ];

        $sosialisasis = Sosialisasi::with('pesertas.objekPengawasan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::view('pdf.laporan-sosialisasi', [
            'rekap' => $rekap,
            'sosialisasis' => $sosialisasis,
            'bulan' => $startDate->translatedFormat('F Y'),
        ]);

        return $pdf->download('laporan-sosialisasi-'.$bulan.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $sosialisasis = Sosialisasi::with('pesertas.objekPengawasan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $filename = 'laporan-sosialisasi-'.$bulan.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sosialisasis) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['=== REKAP SOSIALISASI ===']);
            fputcsv($file, ['Total Kegiatan', $sosialisasis->count()]);
            fputcsv($file, ['Total Peserta', $sosialisasis->sum(fn ($s) => $s->pesertas->count())]);
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR SOSIALISASI ===']);
            fputcsv($file, ['Judul', 'Tanggal', 'Materi', 'Jumlah Peserta', 'Evaluasi']);

            foreach ($sosialisasis as $row) {
                fputcsv($file, [
                    $row->judul,
                    $row->tanggal->format('d M Y'),
                    Str::limit($row->materi, 50),
                    $row->pesertas->count(),
                    $row->hasil_evaluasi ?? '-',
                ]);
            }
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR PESERTA ===']);
            fputcsv($file, ['Sosialisasi', 'Perusahaan', 'Sertifikat']);

            foreach ($sosialisasis as $sosialisasi) {
                foreach ($sosialisasi->pesertas as $peserta) {
                    fputcsv($file, [
                        $sosialisasi->judul,
                        $peserta->objekPengawasan?->nama_perusahaan ?? '-',
                        $peserta->sertifikat_path ? 'Sudah Diterbitkan' : 'Belum',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
