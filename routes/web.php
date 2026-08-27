<?php

use App\Enums\ArtikelStatus;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExternalArticleMetadataController;
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
use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use App\Services\StatistikService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    // DB berada di host remote (Neon) — cache hasil query homepage agar TTFB
    // tidak membayar 3-5 round-trip database pada setiap request.
    return view('welcome', [
        'statistik' => app(StatistikService::class)->summary(),
        'artikels' => Cache::remember('artikel:beranda', now()->addMinutes(30), fn () => Artikel::published()->latest('tanggal_publish')->take(6)->with('user')->get()),
    ]);
})->name('home');

// /login tidak dialihkan ke panel admin — biarkan 404 agar lokasi panel
// (prefix dari ADMIN_PATH) tidak bocor lewat redirect. Nama route 'login'
// tidak dipakai kode mana pun; guest middleware memakai redirectGuestsTo.

Route::redirect('/lapor', '/pengaduan');

Route::get('/lacak', fn () => view('public.lacak'))->middleware('throttle:30,1');

Route::get('/pengaduan', fn () => view('public.pengaduan'));

Route::get('/profil', fn () => view('public.profil'));

Route::get('/tentang', fn () => view('public.tentang-kami'));

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// UPTD
Route::get('/uptd/lab-lingkungan', fn () => view('public.uptd-lab-lingkungan'));
Route::get('/uptd/jurnal-lab', fn () => view('public.coming-soon', ['title' => 'Jurnal Lab']));
Route::get('/uptd/tpa-kawatuna', fn () => view('public.tpa-kawatuna'));
Route::get('/uptd/tpa-kawatuna/sejarah', fn () => view('public.tpa-kawatuna-sejarah'));

// Tata Lingkungan — dokumen publik dari Google Drive
Route::get('/tata-lingkungan', [App\Http\Controllers\TataLingkunganController::class, 'index'])
    ->name('tata-lingkungan')
    ->middleware('throttle:60,1');
Route::get('/api/tata-lingkungan/folders', [App\Http\Controllers\TataLingkunganController::class, 'folders'])
    ->name('tata-lingkungan.folders')
    ->middleware('throttle:60,1');
Route::get('/api/tata-lingkungan/files', [App\Http\Controllers\TataLingkunganController::class, 'files'])
    ->name('tata-lingkungan.files')
    ->middleware('throttle:120,1');

// Tata Penataan Public Routes
Route::redirect('/pengaduan-tata-penataan', '/pengaduan?bidang=tata-penataan');
// Halaman cek lama sudah dipindahkan ke /lacak — link lama tetap berfungsi via redirect.
Route::redirect('/cek-pengaduan-tata-penataan', '/lacak');

Route::post('/feedback/{nomor_tiket}', [FeedbackController::class, 'store'])->middleware('throttle:30,1')->name('feedback.store');

Route::prefix(config('app.admin_path'))->name('admin.')->group(function () {
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->middleware('throttle:30,1')->name('login');
        // Batasi percobaan login per identitas + IP, dengan batas IP global.
        // Ini mencegah satu admin di jaringan/proxy yang sama mengunci admin lain.
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::middleware(['auth', 'admin.access', 'no-store'])->prefix(config('app.admin_path'))->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

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

    // A. Audit / Activity Log (superadmin only — dicek di controller)
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/export', [ActivityLogController::class, 'export'])->name('activity-log.export');

    // B. Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // C. Profil / Pengaturan / Bantuan
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/ai-providers', [SettingController::class, 'storeProvider'])->name('settings.providers.store');
    Route::post('/settings/ai-providers/models', [SettingController::class, 'fetchModels'])->name('settings.providers.models');
    Route::put('/settings/ai-providers/{provider}', [SettingController::class, 'updateProvider'])->name('settings.providers.update');
    Route::delete('/settings/ai-providers/{provider}', [SettingController::class, 'destroyProvider'])->name('settings.providers.destroy');
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

    // E. Backup / Restore Database (superadmin only — dicek di controller)
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
    Route::get('/backup/progress', [BackupController::class, 'progress'])->name('backup.progress');
    Route::post('/backup/cancel', [BackupController::class, 'cancel'])->name('backup.cancel');
    Route::get('/backup/{file}/download', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    Route::post('/backup/bulk-delete', [BackupController::class, 'destroyMany'])->name('backup.destroy-many');
    Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');

    // D. (Import dihapus) — export tetap di bawah wildcard /{resource}

    // F. Komentar Artikel — infinite scroll + bulk
    Route::get('/artikel/{artikel}/komentar', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'index'])->name('artikel.komentar.index');
    Route::post('/artikel/{artikel}/komentar', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'store'])->name('artikel.komentar.store');
    Route::delete('/artikel/{artikel}/komentar/bulk', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'bulkDestroy'])->name('artikel.komentar.bulkDestroy');
    Route::post('/artikel/{artikel}/komentar/{id}/hide', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'toggleHide'])->name('artikel.komentar.hide');
    Route::post('/artikel/{artikel}/komentar/{id}/pin', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'togglePin'])->name('artikel.komentar.pin');
    Route::delete('/artikel/{artikel}/komentar/{id}', [\App\Http\Controllers\Admin\ArtikelKomentarAdminController::class, 'destroy'])->name('artikel.komentar.destroy');

    // G. Ulasan Masyarakat
    Route::get('/ulasan-masyarakat', [\App\Http\Controllers\Admin\UlasanMasyarakatController::class, 'index'])->name('ulasan-masyarakat.index');

    Route::post('/upload-image', [\App\Http\Controllers\Admin\UploadController::class, 'uploadImage'])->name('upload-image');

    // Harus sebelum wildcard /{resource}: preview hanya fetch dan tidak menyimpan data.
    Route::post('/artikel/metadata/preview', ExternalArticleMetadataController::class)
        ->middleware('throttle:20,1')
        ->name('artikel.metadata.preview');

    // Unduh dokumen/lampiran dari storage publik (field file & relasi dokumen)
    // Didefinisikan sebelum wildcard /{resource} agar tidak bentrok dengan route tersebut.
    Route::get('/file/download', [ResourceController::class, 'downloadFile'])->name('file.download');

    Route::get('/{resource}/export', [ResourceController::class, 'export'])->name('resources.export');
    Route::get('/{resource}/export-all', [ResourceController::class, 'exportAll'])->name('resources.export-all');
    Route::get('/{resource}/bulk-export', [ResourceController::class, 'bulkExport'])->name('resources.bulk-export');
    Route::get('/exports/download/{token}', [ResourceController::class, 'downloadExport'])->name('exports.download');
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

// Akses sertifikat via token acak (bukan ID) agar tidak bisa dienumerasi (IDOR).
Route::get('/sosialisasi/{sosialisasi}/sertifikat/{token}.pdf', function (Sosialisasi $sosialisasi, string $token) {
    $peserta = SosialisasiPeserta::where('sosialisasi_id', $sosialisasi->id)
        ->where('token', $token)
        ->firstOrFail();
    $peserta->load('objekPengawasan');
    $pdf = Pdf::loadView('pdf.sertifikat-sosialisasi', compact('sosialisasi', 'peserta'));

    return $pdf->download('sertifikat-'.$peserta->objekPengawasan?->nama_perusahaan.'-'.$sosialisasi->id.'.pdf');
})->middleware('throttle:10,1');

// Pengendalian Public Routes
Route::redirect('/pengaduan-pengendalian', '/pengaduan?bidang=pengendalian');
// Halaman cek lama sudah dipindahkan ke /lacak — link lama tetap berfungsi via redirect.
Route::redirect('/cek-pengaduan-pengendalian', '/lacak');
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
Route::redirect('/pengaduan-rth', '/pengaduan?bidang=rth');
// Halaman cek lama sudah dipindahkan ke /lacak — link lama tetap berfungsi via redirect.
Route::redirect('/cek-pengaduan-rth', '/lacak');
Route::get('/pinjam-taman', fn () => view('public.pinjam-taman'));
Route::get('/cek-pinjam-taman', fn () => view('public.cek-pinjam-taman'))->middleware('throttle:30,1');

// Sampah & LB3 Public Routes
Route::get('/peta-persampahan', [\App\Http\Controllers\PetaPersampahanController::class, 'index'])->middleware('throttle:60,1');
Route::get('/api/peta-persampahan/layers', [\App\Http\Controllers\PetaPersampahanController::class, 'layers'])->middleware('throttle:120,1');
Route::redirect('/pengaduan-sampah', '/pengaduan?bidang=sampah');
// Halaman cek lama sudah dipindahkan ke /lacak — link lama tetap berfungsi via redirect.
Route::redirect('/cek-pengaduan-sampah', '/lacak');
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
})->middleware('throttle:60,1');

Route::get('/berita/{slug}', function (string $slug) {
    $artikel = Artikel::published()
        ->where('slug', $slug)
        ->firstOrFail();

    if ($artikel->isExternal()) {
        return redirect()->away($artikel->external_url);
    }

    return view('public.berita.show', compact('artikel'));
})->middleware('throttle:60,1');

// Komentar Berita (public)
Route::get('/api/berita/{slug}/komentar', [App\Http\Controllers\ArtikelKomentarController::class, 'index'])->middleware('throttle:60,1')->name('berita.komentar.index');
Route::get('/api/berita/{slug}/komentar/count', [App\Http\Controllers\ArtikelKomentarController::class, 'count'])->middleware('throttle:120,1')->name('berita.komentar.count');
Route::post('/api/berita/{slug}/komentar', [App\Http\Controllers\ArtikelKomentarController::class, 'store'])->middleware('throttle:30,1')->name('berita.komentar.store');
Route::post('/api/komentar/{id}/reaction', [App\Http\Controllers\ArtikelKomentarController::class, 'toggleReaction'])->middleware('throttle:60,1')->name('berita.komentar.reaction');

Route::get('/api/armada-aktif', function () {
    return response()->json([
        'status' => true,
        'message' => 'Daftar armada berhasil diambil.',
        // Hanya kolom publik — 'raw_data' tidak boleh bocor ke pengunjung.
        'data' => \App\Models\GpsVehicleCache::select(\App\Models\GpsVehicleCache::PUBLIC_COLUMNS)->get(),
    ]);
})->middleware('throttle:60,1');

// Chatbot streaming endpoint
Route::post('/api/chatbot/stream', [App\Http\Controllers\ChatStreamController::class, 'stream'])
    ->middleware('throttle:20,1');

// Permanent proxy for OpenGraph/social-share images stored in the (private) B2 bucket.
// Avoids short-lived signed URLs that break WhatsApp/Facebook previews.
Route::get('/file/og', [App\Http\Controllers\OgImageProxyController::class, 'show'])
    ->name('og.image')
    ->middleware('throttle:60,1');

// Kontak pelaporan kerentanan keamanan (praktik baik website pemerintah).
Route::get('/.well-known/security.txt', function () {
    return response(implode("\n", [
        'Contact: mailto:admin@dlhpalu.go.id',
        'Expires: '.now()->addYear()->format('Y-m-d\T00:00:00P'),
        'Preferred-Languages: id, en',
    ]), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
});

// Preview inline dokumen/lampiran via proxy web (URL bersih domain sendiri,
// bukan URL signed B2). Wajib login admin. Didefinisikan PALING AKHIR agar
// route spesifik (mis. /berita/{slug}) tetap menang prioritas pencocokan.
// {file} = nama file (basename), tanpa subdirektori maupun sufiks.
Route::get('/{resource}/{file}', [ResourceController::class, 'previewFile'])
    ->name('file.preview')
    ->middleware(['auth', 'admin.access', 'no-store'])
    ->where('file', '.*');
