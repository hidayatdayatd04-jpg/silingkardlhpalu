<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaduanTataPenataan;
use App\Models\Sidak;
use App\Models\ObjekPengawasan;
use Illuminate\Http\Request;

class PengaduanSidakController extends Controller
{
    /**
     * Form untuk membuat sidak dari pengaduan
     */
    public function createSidakFromPengaduan(PengaduanTataPenataan $pengaduan)
    {
        $pengaduan->load('fotos');

        // Cari objek pengawasan berdasarkan nama perusahaan terlapor
        $objekPengawasan = null;
        if ($pengaduan->nama_perusahaan_terlapor) {
            $objekPengawasan = ObjekPengawasan::where('nama_perusahaan', 'LIKE', "%{$pengaduan->nama_perusahaan_terlapor}%")->first();
        }

        return view('admin.pengaduan-sidak.create', compact('pengaduan', 'objekPengawasan'));
    }

    /**
     * Store sidak dari pengaduan
     */
    public function storeSidakFromPengaduan(Request $request, PengaduanTataPenataan $pengaduan)
    {
        $validated = $request->validate([
            'objek_pengawasan_id' => 'required|exists:objek_pengawasans,id',
            'tanggal_sidak' => 'required|date',
            'nama_petugas' => 'required|string|max:255',
            'catatan_jadwal' => 'nullable|string',
        ]);

        // Buat sidak baru
        $sidak = Sidak::create([
            'objek_pengawasan_id' => $validated['objek_pengawasan_id'],
            'pengaduan_tata_penataan_id' => $pengaduan->id,
            'tanggal_sidak' => $validated['tanggal_sidak'],
            'nama_petugas' => $validated['nama_petugas'],
            'user_id' => auth()->id(),
            'is_jadwal' => true,
            'status_tindak_lanjut' => 'belum',
            'catatan_jadwal' => $validated['catatan_jadwal'] ?? "Dijadwalkan dari pengaduan {$pengaduan->nomor_tiket}",
        ]);

        // Update status pengaduan menjadi "ditugaskan"
        $pengaduan->update([
            'status' => 'ditugaskan',
            'catatan_admin' => ($pengaduan->catatan_admin ? $pengaduan->catatan_admin . "\n" : '') 
                . "Sidak dijadwalkan pada " . now()->format('d M Y H:i') . " oleh " . auth()->user()->name,
        ]);

        return redirect()->route('admin.resources.show', ['sidak', $sidak])
            ->with('success', "Sidak berhasil dijadwalkan dari pengaduan {$pengaduan->nomor_tiket}");
    }
}
