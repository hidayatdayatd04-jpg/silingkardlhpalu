<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PengaduanTataPenataan;
use App\Models\Sanksi;
use App\Models\Sidak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanTataPenataanController extends Controller
{
    public function index()
    {
        $bulan = request('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $rekap = [
            'pengaduan' => PengaduanTataPenataan::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sidak' => Sidak::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pelanggaran' => Pelanggaran::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sanksi' => Sanksi::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sanksi_selesai' => Sanksi::where('status_sanksi', 'selesai')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $tren = collect(range(5, 0))->map(fn ($i) => [
            'bulan' => Carbon::now()->subMonths($i)->translatedFormat('M Y'),
            'pengaduan' => PengaduanTataPenataan::whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                ->whereYear('created_at', Carbon::now()->subMonths($i)->year)->count(),
            'sidak' => Sidak::whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                ->whereYear('created_at', Carbon::now()->subMonths($i)->year)->count(),
            'pelanggaran' => Pelanggaran::whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                ->whereYear('created_at', Carbon::now()->subMonths($i)->year)->count(),
        ]);

        $distribusiPelanggaran = Pelanggaran::select('jenis_pelanggaran', DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', now()->year)
            ->groupBy('jenis_pelanggaran')
            ->pluck('total', 'jenis_pelanggaran');

        $distribusiSidak = Sidak::select('hasil', DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', now()->year)
            ->groupBy('hasil')
            ->pluck('total', 'hasil');

        return view('admin.laporan-tata-penataan.index', compact(
            'rekap', 'tren', 'distribusiPelanggaran', 'distribusiSidak', 'bulan'
        ));
    }

    public function exportPdf()
    {
        $bulan = request('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $rekap = [
            'pengaduan' => PengaduanTataPenataan::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sidak' => Sidak::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pelanggaran' => Pelanggaran::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sanksi' => Sanksi::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-tata-penataan', [
            'rekap' => $rekap,
            'bulan' => $startDate->translatedFormat('F Y'),
        ]);

        return $pdf->download('laporan-tata-penataan-'.$bulan.'.pdf');
    }

    public function exportExcel()
    {
        $bulan = request('bulan', now()->format('Y-m'));
        $startDate = Carbon::parse($bulan)->startOfMonth();
        $endDate = Carbon::parse($bulan)->endOfMonth();

        $filename = 'laporan-tata-penataan-'.$bulan.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $pengaduan = PengaduanTataPenataan::whereBetween('created_at', [$startDate, $endDate])->get();
        $sidak = Sidak::whereBetween('created_at', [$startDate, $endDate])->get();
        $pelanggaran = Pelanggaran::whereBetween('created_at', [$startDate, $endDate])->get();
        $sanksi = Sanksi::whereBetween('created_at', [$startDate, $endDate])->get();

        $callback = function () use ($pengaduan, $sidak, $pelanggaran, $sanksi) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['=== REKAP BULANAN ===']);
            fputcsv($file, ['Total Pengaduan', $pengaduan->count()]);
            fputcsv($file, ['Total Sidak', $sidak->count()]);
            fputcsv($file, ['Total Pelanggaran', $pelanggaran->count()]);
            fputcsv($file, ['Total Sanksi', $sanksi->count()]);
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR PENGADUAN ===']);
            fputcsv($file, ['Nomor Tiket', 'Pelapor', 'Jenis', 'Status', 'Tanggal']);
            foreach ($pengaduan as $row) {
                fputcsv($file, [$row->nomor_tiket, $row->nama_pelapor, $row->jenis_pengaduan, $row->status, $row->created_at->format('d M Y')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR SIDAK ===']);
            fputcsv($file, ['Tanggal', 'Objek', 'Hasil', 'Status']);
            foreach ($sidak as $row) {
                fputcsv($file, [$row->tanggal_sidak, $row->objekPengawasan?->nama_perusahaan, $row->hasil, $row->status_tindak_lanjut]);
            }
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR PELANGGARAN ===']);
            fputcsv($file, ['Jenis', 'Keterangan', 'Objek', 'Tanggal']);
            foreach ($pelanggaran as $row) {
                fputcsv($file, [$row->jenis_pelanggaran, $row->keterangan, $row->objekPengawasan?->nama_perusahaan, $row->created_at->format('d M Y')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['=== DAFTAR SANKSI ===']);
            fputcsv($file, ['Jenis', 'Status', 'Batas Waktu', 'Objek', 'Tanggal']);
            foreach ($sanksi as $row) {
                fputcsv($file, [$row->jenis_sanksi, $row->status_sanksi, $row->batas_waktu_perbaikan?->format('d M Y'), $row->pelanggaran?->objekPengawasan?->nama_perusahaan, $row->created_at->format('d M Y')]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
