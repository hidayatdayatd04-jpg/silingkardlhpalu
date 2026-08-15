<?php

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
use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use App\Services\StatistikService;
use App\Support\ProfileMarkdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome', [
        'profil' => ProfilDinas::current(),
        'statistik' => app(StatistikService::class)->summary(),
        'artikels' => Artikel::published()->latest('tanggal_publish')->take(6)->with('user')->get(),
    ]);
});

Route::redirect('/login', '/admin/login')->name('login');

Route::redirect('/lapor', '/pengaduan');

Route::get('/lacak', fn () => view('public.lacak'))->middleware('throttle:30,1');

Route::get('/pengaduan', fn () => view('public.pengaduan'));

Route::get('/armada', fn () => view('public.armada'));

Route::get('/profil', fn () => view('public.profil', [
    'profil' => ProfileMarkdown::load() ?? ProfilDinas::current(),
]));

Route::get('/tentang', fn () => view('public.tentang-kami'));

// Sekretariat & UPTD
Route::get('/sekretariat', fn () => view('public.coming-soon', ['title' => 'Sekretariat']));
Route::get('/uptd/{slug}', function (string $slug) {
    $titles = [
        'topoksi-lab' => 'Topoksi Lab',
        'jurnal-lab' => 'Jurnal Lab',
        'tpa-kawatuna' => 'UPTD TPA Kawatuna',
    ];
    $title = $titles[$slug] ?? 'UPTD';
    if ($slug === 'topoksi-lab') {
        return view('public.topoksi-lab');
    }
    return view('public.coming-soon', ['title' => $title]);
})->whereIn('slug', ['topoksi-lab', 'jurnal-lab', 'tpa-kawatuna']);

Route::get('/tata-penataan', fn () => view('public.tata-penataan'));

// Tata Lingkungan — dokumen publik dari Google Drive
Route::get('/tata-lingkungan', [App\Http\Controllers\TataLingkunganController::class, 'index'])->name('tata-lingkungan');
Route::get('/api/tata-lingkungan/folders', [App\Http\Controllers\TataLingkunganController::class, 'folders'])
    ->name('tata-lingkungan.folders')
    ->middleware('throttle:60,1');
Route::get('/api/tata-lingkungan/files', [App\Http\Controllers\TataLingkunganController::class, 'files'])
    ->name('tata-lingkungan.files')
    ->middleware('throttle:120,1');

// Tata Penataan Public Routes
Route::get('/pengaduan-tata-penataan', fn () => view('public.pengaduan-tata-penataan'));
Route::get('/cek-pengaduan-tata-penataan', fn () => view('public.cek-pengaduan-tata-penataan'))->middleware('throttle:30,1');
Route::get('/peta-objek-pengawasan', fn () => view('public.peta-objek-pengawasan'));

Route::post('/feedback/{nomor_tiket}', [FeedbackController::class, 'store'])->middleware('throttle:30,1')->name('feedback.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['guest', 'throttle:30,1'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Kesekretariatan per bidang (admin) — halaman Segera Hadir
    Route::get('/sekretariat-pengendalian', fn () => view('admin.sekretariat.index', ['title' => 'Kesekretariatan - Pengendalian']))->name('sekretariat.pengendalian');
    Route::get('/sekretariat-sampah-lb3', fn () => view('admin.sekretariat.index', ['title' => 'Kesekretariatan - Sampah & LB3']))->name('sekretariat.sampah-lb3');
    Route::get('/sekretariat-tata-penataan', fn () => view('admin.sekretariat.index', ['title' => 'Kesekretariatan - Tata Penataan']))->name('sekretariat.tata-penataan');
    Route::get('/sekretariat-rth', fn () => view('admin.sekretariat.index', ['title' => 'Kesekretariatan - RTH']))->name('sekretariat.rth');
    // Kesekretariatan (Konten & Sistem) — halaman Segera Hadir
    Route::get('/sekretariat', fn () => view('admin.sekretariat.index', ['title' => 'Kesekretariatan']))->name('sekretariat');

    // Peta Laporan — sebaran pengaduan berkoordinat (hanya admin, akses per bidang)
    Route::get('/peta-laporan/data', [\App\Http\Controllers\PetaLaporanController::class, 'data'])
        ->name('peta-laporan.data')
        ->middleware('throttle:30,1');

    // Peta (GIS) Routes - unified page
    Route::get('/peta', [\App\Http\Controllers\Admin\PetaController::class, 'index'])->name('peta.index');
    Route::get('/peta/layers', [\App\Http\Controllers\Admin\PetaController::class, 'layers'])->name('peta.layers');
    Route::post('/peta/import', [\App\Http\Controllers\Admin\PetaController::class, 'import'])->name('peta.import');
    Route::post('/peta/layers', [\App\Http\Controllers\Admin\PetaController::class, 'storeLayer'])->name('peta.layers.store');
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

    Route::get('/ulasan-masyarakat', [\App\Http\Controllers\Admin\UlasanMasyarakatController::class, 'index'])->name('ulasan-masyarakat.index');

    // A. Audit / Activity Log (superadmin only — dicek di controller)
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/export', [ActivityLogController::class, 'export'])->name('activity-log.export');

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

    // D. (Import dihapus) — export tetap di bawah wildcard /{resource}

    // F. Ulasan Masyarakat
    Route::get('/ulasan-masyarakat', [\App\Http\Controllers\Admin\UlasanMasyarakatController::class, 'index'])->name('ulasan-masyarakat.index');

    Route::post('/upload-image', [\App\Http\Controllers\Admin\UploadController::class, 'uploadImage'])->name('upload-image');

    // Unduh dokumen/lampiran dari storage publik (field file & relasi dokumen)
    // Didefinisikan sebelum wildcard /{resource} agar tidak bentrok dengan route tersebut.
    Route::get('/file/download', [ResourceController::class, 'downloadFile'])->name('file.download');

    Route::get('/{resource}/export', [ResourceController::class, 'export'])->name('resources.export');
    Route::get('/{resource}/export-all', [ResourceController::class, 'exportAll'])->name('resources.export-all');
    Route::get('/{resource}/bulk-export', [ResourceController::class, 'bulkExport'])->name('resources.bulk-export');
    Route::delete('/{resource}/bulk-delete', [ResourceController::class, 'bulkDelete'])->name('resources.bulk-delete');

    // Reset password pengguna (khusus superadmin) — agar tidak terkunci jika lupa password.
    Route::post('/user/{record}/reset-password', [ResourceController::class, 'resetPassword'])->name('user.reset-password');

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
Route::get('/pengaduan-rth', fn () => view('public.pengaduan-rth'));
Route::get('/cek-pengaduan-rth', fn () => view('public.cek-pengaduan-rth'))->middleware('throttle:30,1');
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
    return view('public.berita.index', [
        'artikels' => Artikel::published()->latest('tanggal_publish')->paginate(9),
    ]);
});

Route::get('/berita/{slug}', function (string $slug) {
    $artikel = Artikel::published()
        ->where('slug', $slug)
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

// Permanent proxy for OpenGraph/social-share images stored in the (private) B2 bucket.
// Avoids short-lived signed URLs that break WhatsApp/Facebook previews.
Route::get('/file/og', [App\Http\Controllers\OgImageProxyController::class, 'show'])
    ->name('og.image');
