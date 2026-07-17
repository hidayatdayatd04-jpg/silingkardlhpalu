<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObjekPengawasan;
use App\Models\Sidak;
use App\Models\Pelanggaran;
use App\Models\Sanksi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanKetaatanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        // Total objek pengawasan
        $totalObjek = ObjekPengawasan::count();

        // Sidak bulan ini
        $sidaks = Sidak::whereBetween('tanggal_sidak', [$startDate, $endDate])->get();
        $totalSidak = $sidaks->count();

        // Hitung ketaatan
        $taat = $sidaks->where('hasil', 'taat')->count();
        $tidakTaat = $sidaks->where('hasil', 'tidak_taat')->count();
        $perluPembinaan = $sidaks->where('hasil', 'perlu_pembinaan')->count();

        // Persentase ketaatan
        $persentaseKetaatan = $totalSidak > 0 ? round(($taat / $totalSidak) * 100, 1) : 0;

        // Rekap
        $rekap = [
            'total_objek' => $totalObjek,
            'total_sidak' => $totalSidak,
            'taat' => $taat,
            'tidak_taat' => $tidakTaat,
            'perlu_pembinaan' => $perluPembinaan,
            'persentase_ketaatan' => $persentaseKetaatan,
            'total_pelanggaran' => Pelanggaran::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sanksi' => Sanksi::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // Tren ketaatan 6 bulan terakhir
        $tren = collect(range(5, 0))->map(function ($i) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            
            $sidaks = Sidak::whereBetween('tanggal_sidak', [$startDate, $endDate])->get();
            $total = $sidaks->count();
            $taat = $sidaks->where('hasil', 'taat')->count();
            
            return [
                'bulan' => Carbon::now()->subMonths($i)->translatedFormat('M Y'),
                'total' => $total,
                'taat' => $taat,
                'persentase' => $total > 0 ? round(($taat / $total) * 100, 1) : 0,
            ];
        });

        // Ketaatan per objek pengawasan
        $ketaatanPerObjek = ObjekPengawasan::withCount(['sidaks as total_sidak' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_sidak', [$startDate, $endDate]);
        }])
        ->withCount(['sidaks as sidak_taat' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_sidak', [$startDate, $endDate])
              ->where('hasil', 'taat');
        }])
        ->having('total_sidak', '>', 0)
        ->orderByDesc('sidak_taat')
        ->take(20)
        ->get()
        ->map(function ($objek) {
            $objek->persentase = $objek->total_sidak > 0 
                ? round(($objek->sidak_taat / $objek->total_sidak) * 100, 1) 
                : 0;
            return $objek;
        });

        // Distribusi hasil sidak
        $distribusiHasil = $sidaks->groupBy('hasil')->map(function ($items, $key) {
            return [
                'label' => match($key) {
                    'taat' => 'Taat',
                    'tidak_taat' => 'Tidak Taat',
                    'perlu_pembinaan' => 'Perlu Pembinaan',
                    default => $key,
                },
                'total' => $items->count(),
            ];
        })->values();

        return view('admin.laporan-ketaatan.index', compact(
            'rekap', 'tren', 'ketaatanPerObjek', 'distribusiHasil', 'bulan'
        ));
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $sidaks = Sidak::whereBetween('tanggal_sidak', [$startDate, $endDate])->get();

        $rekap = [
            'total_sidak' => $sidaks->count(),
            'taat' => $sidaks->where('hasil', 'taat')->count(),
            'tidak_taat' => $sidaks->where('hasil', 'tidak_taat')->count(),
            'perlu_pembinaan' => $sidaks->where('hasil', 'perlu_pembinaan')->count(),
            'persentase_ketaatan' => $sidaks->count() > 0 
                ? round(($sidaks->where('hasil', 'taat')->count() / $sidaks->count()) * 100, 1) 
                : 0,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::view('pdf.laporan-ketaatan', [
            'rekap' => $rekap,
            'bulan' => $startDate->translatedFormat('F Y'),
        ]);

        return $pdf->download('laporan-ketaatan-'.$bulan.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $sidaks = Sidak::with('objekPengawasan')->whereBetween('tanggal_sidak', [$startDate, $endDate])->get();

        $filename = 'laporan-ketaatan-'.$bulan.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sidaks) {
            $file = fopen('php://output', 'w');

            // Hitung rekap
            $taat = $sidaks->where('hasil', 'taat')->count();
            $total = $sidaks->count();

            fputcsv($file, ['=== REKAP KETAATAN ===']);
            fputcsv($file, ['Total Sidak', $total]);
            fputcsv($file, ['Taat', $taat]);
            fputcsv($file, ['Tidak Taat', $sidaks->where('hasil', 'tidak_taat')->count()]);
            fputcsv($file, ['Perlu Pembinaan', $sidaks->where('hasil', 'perlu_pembinaan')->count()]);
            fputcsv($file, ['Persentase Ketaatan', $total > 0 ? round(($taat / $total) * 100, 1).'%' : '0%']);
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR SIDAK ===']);
            fputcsv($file, ['Tanggal', 'Objek Pengawasan', 'Hasil', 'Status']);

            foreach ($sidaks as $row) {
                fputcsv($file, [
                    $row->tanggal_sidak->format('d M Y'),
                    $row->objekPengawasan?->nama_perusahaan ?? '-',
                    $row->hasil_label ?? $row->hasil ?? '-',
                    $row->status_tindak_lanjut?->label() ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
