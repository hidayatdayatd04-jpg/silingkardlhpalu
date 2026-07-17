<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MonitoringSanksiController extends Controller
{
    public function index()
    {
        $totalPelanggaran = Pelanggaran::count();
        $totalSanksi = Sanksi::count();
        $sanksiAktif = Sanksi::where('status_sanksi', '!=', 'selesai')->count();
        $sanksiTerlambat = Sanksi::whereNotNull('batas_waktu_perbaikan')
            ->where('batas_waktu_perbaikan', '<', now())
            ->where('status_sanksi', '!=', 'selesai')
            ->count();

        $grafikPelanggaran = Pelanggaran::select('jenis_pelanggaran', DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', now()->year)
            ->groupBy('jenis_pelanggaran')
            ->pluck('total', 'jenis_pelanggaran');

        $sanksiMendekatiJatuhTempo = Sanksi::with('pelanggaran.objekPengawasan')
            ->whereNotNull('batas_waktu_perbaikan')
            ->where('batas_waktu_perbaikan', '>=', now())
            ->where('batas_waktu_perbaikan', '<=', now()->addDays(7))
            ->where('status_sanksi', '!=', 'selesai')
            ->orderBy('batas_waktu_perbaikan')
            ->get();

        $pipeline = Pelanggaran::with('sanksi.objekPengawasan')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.monitoring-sanksi.index', compact(
            'totalPelanggaran', 'totalSanksi', 'sanksiAktif', 'sanksiTerlambat',
            'grafikPelanggaran', 'sanksiMendekatiJatuhTempo', 'pipeline'
        ));
    }

    public function export()
    {
        $data = Sanksi::with('pelanggaran.objekPengawasan')
            ->latest()
            ->get();

        $filename = 'monitoring-sanksi-'.now()->format('Ymd-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Sanksi', 'Jenis Sanksi', 'Status', 'Batas Waktu', 'Objek Pengawasan', 'Jenis Pelanggaran', 'Dibuat']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->jenis_sanksi,
                    $row->status_sanksi,
                    $row->batas_waktu_perbaikan?->format('d M Y'),
                    $row->pelanggaran?->objekPengawasan?->nama_perusahaan,
                    $row->pelanggaran?->jenis_pelanggaran,
                    $row->created_at?->format('d M Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Cek sanksi yang mendekati jatuh tempo atau sudah terlambat
     * dan kirim notifikasi ke admin
     */
    public function checkOverdue(Request $request)
    {
        // Sanksi yang sudah terlambat (melewati batas waktu)
        $sanksiTerlambat = Sanksi::with('pelanggaran.objekPengawasan')
            ->whereNotNull('batas_waktu_perbaikan')
            ->where('batas_waktu_perbaikan', '<', now())
            ->where('status_sanksi', '!=', 'selesai')
            ->get();

        // Sanksi yang mendekati jatuh tempo (3 hari lagi)
        $sanksiMendekatiJatuhTempo = Sanksi::with('pelanggaran.objekPengawasan')
            ->whereNotNull('batas_waktu_perbaikan')
            ->where('batas_waktu_perbaikan', '>=', now())
            ->where('batas_waktu_perbaikan', '<=', now()->addDays(3))
            ->where('status_sanksi', '!=', 'selesai')
            ->get();

        // Kirim notifikasi untuk sanksi terlambat
        $admins = User::where('is_active', true)->get();
        $notifiedCount = 0;

        foreach ($sanksiTerlambat as $sanksi) {
            $judul = 'Sanksi Terlambat!';
            $pesan = sprintf(
                'Sanksi untuk %s sudah melewati batas waktu perbaikan (%s). Segera tindaklanjuti!',
                $sanksi->pelanggaran?->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui',
                $sanksi->batas_waktu_perbaikan->format('d M Y')
            );

            // Cek apakah sudah ada notifikasi untuk sanksi ini hari ini
            $alreadyNotified = $admins->every(function ($admin) use ($sanksi) {
                return $admin->notifications()
                    ->where('data->type', 'sanksi_overdue')
                    ->where('data->sanksi_id', $sanksi->id)
                    ->whereDate('created_at', today())
                    ->exists();
            });

            if (! $alreadyNotified) {
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\SanksiJatuhTempoNotification(
                        $sanksi,
                        'overdue'
                    ));
                }
                $notifiedCount++;
            }
        }

        // Kirim notifikasi untuk sanksi mendekati jatuh tempo
        foreach ($sanksiMendekatiJatuhTempo as $sanksi) {
            $hari = $sanksi->batas_waktu_perbaikan->diffInDays(now());
            $judul = 'Sanksi Mendekati Jatuh Tempo';
            $pesan = sprintf(
                'Sanksi untuk %s akan jatuh tempo dalam %d hari lagi (%s).',
                $sanksi->pelanggaran?->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui',
                $hari,
                $sanksi->batas_waktu_perbaikan->format('d M Y')
            );

            $alreadyNotified = $admins->every(function ($admin) use ($sanksi) {
                return $admin->notifications()
                    ->where('data->type', 'sanksi_approaching_deadline')
                    ->where('data->sanksi_id', $sanksi->id)
                    ->whereDate('created_at', today())
                    ->exists();
            });

            if (! $alreadyNotified) {
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\SanksiJatuhTempoNotification(
                        $sanksi,
                        'approaching'
                    ));
                }
                $notifiedCount++;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil mengirim {$notifiedCount} notifikasi.",
                'overdue' => $sanksiTerlambat->count(),
                'approaching' => $sanksiMendekatiJatuhTempo->count(),
            ]);
        }

        return back()->with('success', "Berhasil mengirim {$notifiedCount} notifikasi jatuh tempo sanksi.");
    }
}
