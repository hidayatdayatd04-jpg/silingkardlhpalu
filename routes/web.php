<?php

use App\Http\Controllers\AccessGateController;
use App\Enums\ArtikelStatus;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HelpController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\FeedbackController;
use App\Models\Artikel;
use App\Models\GpsVehicleCache;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanRekomendasi;
use App\Models\ProfilDinas;
use App\Models\Sanksi;
use App\Models\Sidak;
use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use App\Services\StatistikService;
use App\Support\ProfileMarkdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// ══════════════════ Access Gate (site.access middleware di-skip) ══════════════════
Route::withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)->group(function () {
    Route::get('/gate', [AccessGateController::class, 'show'])->name('access-gate.show');
    Route::post('/gate', [AccessGateController::class, 'verify'])->name('access-gate.verify');
    Route::post('/gate/logout', [AccessGateController::class, 'logout'])->name('access-gate.logout');
});

Route::get('/', function () {
    return view('welcome', [
        'profil' => ProfilDinas::current(),
        'statistik' => app(StatistikService::class)->summary(),
        'artikels' => Artikel::published()->latest('tanggal_publish')->take(6)->get(),
    ]);
});

Route::redirect('/login', '/admin/login')->name('login');

// Ganti bahasa situs publik (id/en) — disimpan di sesi, lalu kembali ke halaman asal.
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->whereIn('locale', ['id', 'en'])->name('lang.switch');

Route::redirect('/lapor', '/pengaduan');

Route::get('/lacak', fn () => view('public.lacak'))->middleware('throttle:30,1');

Route::get('/pengaduan', fn () => view('public.pengaduan'));

Route::get('/survei', fn () => view('public.survei'));

Route::get('/armada', fn () => redirect('/peta-persampahan#armada', 301));

Route::get('/profil', fn () => view('public.profil', [
    'profil' => ProfileMarkdown::load() ?? ProfilDinas::current(),
]));

Route::redirect('/tentang', '/profil');

// Sekretariat & UPTD
Route::get('/sekretariat', fn () => view('public.coming-soon', ['title' => 'Sekretariat']));
Route::get('/uptd/{slug}', function (string $slug) {
    $titles = [
        'lab-lingkungan' => 'UPTD Lab Lingkungan',
        'tpa-kawatuna' => 'UPTD TPA Kawatuna',
    ];
    $title = $titles[$slug] ?? 'UPTD';
    return view('public.coming-soon', ['title' => $title]);
})->whereIn('slug', ['lab-lingkungan', 'tpa-kawatuna']);

Route::get('/tata-penataan', fn () => view('public.tata-penataan'));

// Tata Penataan Public Routes
Route::get('/pengaduan-tata-penataan', fn () => view('public.pengaduan-tata-penataan'));
Route::get('/cek-pengaduan-tata-penataan', fn () => view('public.cek-pengaduan-tata-penataan'))->middleware('throttle:30,1');
Route::get('/peta-objek-pengawasan', fn () => view('public.peta-objek-pengawasan'));

Route::post('/feedback/{nomor_tiket}', [FeedbackController::class, 'store'])->middleware('throttle:30,1')->name('feedback.store');

Route::withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)->prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['guest', 'throttle:30,1'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)->middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Peta (GIS) Routes - unified page
    Route::get('/peta', [\App\Http\Controllers\Admin\PetaController::class, 'index'])->name('peta.index');
    Route::get('/peta/layers', [\App\Http\Controllers\Admin\PetaController::class, 'layers'])->name('peta.layers');
    Route::post('/peta/import', [\App\Http\Controllers\Admin\PetaController::class, 'import'])->name('peta.import');
    Route::put('/peta/layer/{layer}', [\App\Http\Controllers\Admin\PetaController::class, 'updateLayer'])->name('peta.layer.update');
    Route::delete('/peta/layer/{layer}', [\App\Http\Controllers\Admin\PetaController::class, 'destroyLayer'])->name('peta.layer.delete');
    Route::post('/peta/layers/bulk-delete', [\App\Http\Controllers\Admin\PetaController::class, 'bulkDestroyLayers'])->name('peta.layers.bulk-delete');
    Route::post('/peta/layers/bulk-visibility', [\App\Http\Controllers\Admin\PetaController::class, 'bulkVisibility'])->name('peta.layers.bulk-visibility');
    Route::post('/peta/layer/{layerId}/restore', [\App\Http\Controllers\Admin\PetaController::class, 'restoreLayer'])->name('peta.layer.restore');
    Route::post('/peta/draw', [\App\Http\Controllers\Admin\PetaController::class, 'saveDrawnFeatures'])->name('peta.draw.save');
    Route::put('/peta/layer/{layer}/feature/{featureIndex}', [\App\Http\Controllers\Admin\PetaController::class, 'updateFeature'])->name('peta.feature.update');
    Route::delete('/peta/layer/{layer}/feature', [\App\Http\Controllers\Admin\PetaController::class, 'deleteFeature'])->name('peta.feature.delete');

    // Component Demo (only for development)
    if (app()->environment('local')) {
        Route::get('/component-demo', fn() => view('admin.component-demo'))->name('component-demo');
    }

    Route::get('/sidak/{sidak}/ba-pdf', function (Sidak $sidak) {
        abort_unless(auth()->user()?->can('view', $sidak), 403);
        $sidak->load(['objekPengawasan', 'petugas', 'media']);
        $pdf = Pdf::loadView('pdf.sidak-berita-acara', compact('sidak'));

        return $pdf->download('ba-sidak-'.$sidak->id.'.pdf');
    })->name('sidak.ba-pdf');

    Route::get('/sanksi/{sanksi}/surat-pdf', function (Sanksi $sanksi) {
        abort_unless(auth()->user()?->can('view', $sanksi), 403);
        $sanksi->load('pelanggaran.objekPengawasan');
        $pdf = Pdf::loadView('pdf.surat-sanksi', compact('sanksi'));

        return $pdf->download('surat-sanksi-'.$sanksi->id.'.pdf');
    })->name('sanksi.surat-pdf');

    Route::get('/sosialisasi-peserta/{peserta}/sertifikat-pdf', function (SosialisasiPeserta $peserta) {
        abort_unless(auth()->user()?->can('view', $peserta->sosialisasi), 403);
        $peserta->load(['objekPengawasan', 'sosialisasi']);
        $sosialisasi = $peserta->sosialisasi;
        $pdf = Pdf::loadView('pdf.sertifikat-sosialisasi', compact('sosialisasi', 'peserta'));
        $filename = 'sertifikat-'.str($peserta->objekPengawasan?->nama_perusahaan ?? 'peserta')->slug().'-'.$peserta->id.'.pdf';
        $path = 'tata-penataan/sertifikat/'.$filename;
        Storage::disk('public')->put($path, $pdf->output());
        $peserta->update(['sertifikat_path' => $path]);

        return $pdf->download($filename);
    })->name('sosialisasi.sertifikat-pdf');

    Route::get('/sosialisasi/{sosialisasi}/sertifikat-all', function (Sosialisasi $sosialisasi) {
        abort_unless(auth()->user()?->can('view', $sosialisasi), 403);
        $sosialisasi->load('pesertas.objekPengawasan');
        $zipName = 'sertifikat-sosialisasi-'.$sosialisasi->id.'.zip';
        $tempPath = storage_path('app/temp/'.$zipName);
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        $zip = new ZipArchive;
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($sosialisasi->pesertas as $peserta) {
            $pdf = Pdf::loadView('pdf.sertifikat-sosialisasi', [
                'sosialisasi' => $sosialisasi,
                'peserta' => $peserta,
            ]);
            $filename = 'sertifikat-'.str($peserta->objekPengawasan?->nama_perusahaan ?? 'peserta-'.$peserta->id)->slug().'.pdf';
            $zip->addFromString($filename, $pdf->output());
            $path = 'tata-penataan/sertifikat/'.$filename;
            Storage::disk('public')->put($path, $pdf->output());
            $peserta->update(['sertifikat_path' => $path]);
        }
        $zip->close();

        return response()->download($tempPath, $zipName)->deleteFileAfterSend(true);
    })->name('sosialisasi.sertifikat-all');

    // ══════════════════ Fitur admin baru (WAJIB sebelum wildcard /{resource}) ══════════════════

    // A. Audit / Activity Log (superadmin only — dicek di controller)
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // B. Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // C. Profil / Pengaturan / Bantuan
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

    // E. Backup / Restore Database (superadmin only — dicek di controller)
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
    Route::get('/backup/{file}/download', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');

    // D. Import generik (sebelum /{resource}/{record})
    Route::get('/{resource}/import-template', [ResourceController::class, 'importTemplate'])->name('resources.import-template');
    Route::post('/{resource}/import', [ResourceController::class, 'import'])->name('resources.import');

    // F. Ulasan Masyarakat
    Route::get('/ulasan-masyarakat', [\App\Http\Controllers\Admin\UlasanMasyarakatController::class, 'index'])->name('ulasan-masyarakat.index');

    Route::post('/upload-image', [\App\Http\Controllers\Admin\UploadController::class, 'uploadImage'])->name('upload-image');

    // G. Monitoring & Laporan (standalone pages, before generic /{resource})
    Route::get('/monitoring-sanksi', [\App\Http\Controllers\Admin\MonitoringSanksiController::class, 'index'])->name('monitoring-sanksi.index');
    Route::get('/monitoring-sanksi/export', [\App\Http\Controllers\Admin\MonitoringSanksiController::class, 'export'])->name('monitoring-sanksi.export');
    Route::post('/monitoring-sanksi/check-overdue', [\App\Http\Controllers\Admin\MonitoringSanksiController::class, 'checkOverdue'])->name('monitoring-sanksi.check-overdue');
    Route::get('/kalender-sidak', [\App\Http\Controllers\Admin\KalenderSidakController::class, 'index'])->name('kalender-sidak.index');
    Route::get('/kalender-sosialisasi', [\App\Http\Controllers\Admin\KalenderSosialisasiController::class, 'index'])->name('kalender-sosialisasi.index');
    Route::get('/pengaduan-tata-penataan/{pengaduan}/buat-sidak', [\App\Http\Controllers\Admin\PengaduanSidakController::class, 'createSidakFromPengaduan'])->name('pengaduan-tata-penataan.buat-sidak');
    Route::get('/laporan-tata-penataan', [\App\Http\Controllers\Admin\LaporanTataPenataanController::class, 'index'])->name('laporan-tata-penataan.index');
    Route::get('/laporan-tata-penataan/export-pdf', [\App\Http\Controllers\Admin\LaporanTataPenataanController::class, 'exportPdf'])->name('laporan-tata-penataan.export-pdf');
    Route::get('/laporan-tata-penataan/export-excel', [\App\Http\Controllers\Admin\LaporanTataPenataanController::class, 'exportExcel'])->name('laporan-tata-penataan.export-excel');
    Route::get('/laporan-sosialisasi', [\App\Http\Controllers\Admin\LaporanSosialisasiController::class, 'index'])->name('laporan-sosialisasi.index');
    Route::get('/laporan-sosialisasi/export-pdf', [\App\Http\Controllers\Admin\LaporanSosialisasiController::class, 'exportPdf'])->name('laporan-sosialisasi.export-pdf');
    Route::get('/laporan-sosialisasi/export-excel', [\App\Http\Controllers\Admin\LaporanSosialisasiController::class, 'exportExcel'])->name('laporan-sosialisasi.export-excel');
    Route::get('/laporan-ketaatan', [\App\Http\Controllers\Admin\LaporanKetaatanController::class, 'index'])->name('laporan-ketaatan.index');
    Route::get('/laporan-ketaatan/export-pdf', [\App\Http\Controllers\Admin\LaporanKetaatanController::class, 'exportPdf'])->name('laporan-ketaatan.export-pdf');
    Route::get('/laporan-ketaatan/export-excel', [\App\Http\Controllers\Admin\LaporanKetaatanController::class, 'exportExcel'])->name('laporan-ketaatan.export-excel');

    Route::get('/{resource}/export', [ResourceController::class, 'export'])->name('resources.export');
    Route::get('/{resource}/export-all', [ResourceController::class, 'exportAll'])->name('resources.export-all');
    Route::get('/{resource}/bulk-export', [ResourceController::class, 'bulkExport'])->name('resources.bulk-export');
    Route::delete('/{resource}/bulk-delete', [ResourceController::class, 'bulkDelete'])->name('resources.bulk-delete');

    Route::get('/{resource}', [ResourceController::class, 'index'])->name('resources.index');
    Route::get('/{resource}/create', [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/{resource}', [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/{resource}/{record}', [ResourceController::class, 'show'])->name('resources.show');
    Route::get('/{resource}/{record}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::put('/{resource}/{record}', [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/{resource}/{record}', [ResourceController::class, 'destroy'])->name('resources.destroy');
});

Route::get('/sosialisasi/{sosialisasi}/sertifikat/{peserta}.pdf', function (Sosialisasi $sosialisasi, SosialisasiPeserta $peserta) {
    abort_unless($peserta->sosialisasi_id === $sosialisasi->id, 404);
    $peserta->load('objekPengawasan');
    $pdf = Pdf::loadView('pdf.sertifikat-sosialisasi', compact('sosialisasi', 'peserta'));

    return $pdf->download('sertifikat-'.$peserta->objekPengawasan?->nama_perusahaan.'-'.$sosialisasi->id.'.pdf');
})->middleware('throttle:10,1');

// Pengendalian Public Routes
Route::get('/pengaduan-pengendalian', fn () => view('public.pengaduan-pengendalian'));
Route::get('/cek-pengaduan-pengendalian', fn () => view('public.cek-pengaduan-pengendalian'))->middleware('throttle:30,1');
Route::get('/permohonan-rekomendasi', fn () => view('public.permohonan-rekomendasi'));
Route::get('/cek-permohonan-rekomendasi', fn () => view('public.cek-permohonan-rekomendasi'))->middleware('throttle:30,1');
Route::get('/permohonan-rekomendasi/{nomor_tiket}/bukti-pdf', function (string $nomor_tiket) {
    $permohonan = PermohonanRekomendasi::with('dokumens')
        ->where('nomor_tiket', $nomor_tiket)
        ->firstOrFail();

    $pdf = Pdf::loadView('pdf.permohonan-rekomendasi-bukti', compact('permohonan'));

    return $pdf->download('bukti-'.$permohonan->nomor_tiket.'.pdf');
})->middleware('throttle:10,1');

Route::get('/kebijakan-privasi', fn () => view('public.kebijakan-privasi'));

Route::get('/syarat-ketentuan', fn () => view('public.syarat-ketentuan'));

// RTH Public Routes
Route::get('/peta-rth', fn () => view('public.peta-rth'));
Route::get('/pengaduan-rth', fn () => view('public.pengaduan-rth'));
Route::get('/cek-pengaduan-rth', fn () => view('public.cek-pengaduan-rth'))->middleware('throttle:30,1');
Route::get('/perizinan-tebang-pohon', fn () => view('public.perizinan-tebang-pohon'));
Route::get('/cek-perizinan-tebang-pohon', fn () => view('public.cek-perizinan-tebang-pohon'))->middleware('throttle:30,1');
Route::get('/pinjam-taman', fn () => view('public.pinjam-taman'));
Route::get('/cek-pinjam-taman', fn () => view('public.cek-pinjam-taman'))->middleware('throttle:30,1');

// Sampah & LB3 Public Routes
Route::get('/peta-persampahan', [\App\Http\Controllers\PetaPersampahanController::class, 'index']);
Route::get('/api/peta-persampahan/layers', [\App\Http\Controllers\PetaPersampahanController::class, 'layers']);
Route::get('/pengaduan-sampah', fn () => view('public.pengaduan-sampah'));
Route::get('/cek-pengaduan-sampah', fn () => view('public.cek-pengaduan-sampah'))->middleware('throttle:30,1');
Route::get('/registrasi-usaha-lb3', fn () => view('public.registrasi-usaha-lb3'));
Route::get('/cek-registrasi-lb3', fn () => view('public.cek-registrasi-lb3'))->middleware('throttle:30,1');
Route::get('/pengajuan-rintek-pertek', fn () => view('public.pengajuan-rintek-pertek'));
Route::get('/cek-rintek-pertek', fn () => view('public.cek-rintek-pertek'))->middleware('throttle:30,1');
Route::get('/pengajuan-rintek-pertek/{nomor_pengajuan}/bukti-pdf', function (string $nomor_pengajuan) {
    $pengajuan = PengajuanRintekPertek::where('nomor_pengajuan', $nomor_pengajuan)->firstOrFail();

    $pdf = Pdf::loadView('pdf.pengajuan-rintek-pertek-bukti', compact('pengajuan'));

    return $pdf->download('bukti-'.$pengajuan->nomor_pengajuan.'.pdf');
})->middleware('throttle:10,1');

// Berita
Route::get('/berita', function () {
    $query = Artikel::published()->latest('tanggal_publish');

    if ($kategori = request('kategori')) {
        $query->where('kategori', $kategori);
    }

    return view('public.berita.index', [
        'artikels' => $query->paginate(9),
    ]);
});

Route::get('/berita/{slug}', function (string $slug) {
    $artikel = Artikel::query()
        ->where('slug', $slug)
        ->where('status', ArtikelStatus::PUBLISHED->value)
        ->whereNotNull('tanggal_publish')
        ->where('tanggal_publish', '<=', now()->toDateString())
        ->firstOrFail();

    return view('public.berita.show', compact('artikel'));
});

Route::get('/api/armada-aktif', function () {
    return response()->json([
        'status' => true,
        'message' => 'Daftar armada berhasil diambil.',
        'data' => \App\Models\GpsVehicleCache::all(),
    ]);
});

// Chatbot streaming endpoint
Route::post('/api/chatbot/stream', [App\Http\Controllers\ChatStreamController::class, 'stream'])
    ->middleware('throttle:20,1');

