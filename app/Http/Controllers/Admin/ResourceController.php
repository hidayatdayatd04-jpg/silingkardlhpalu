<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Enums\ArtikelStatus;
use App\Exceptions\ExternalArticleMetadataException;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\StatusPengaduan;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExportJob;
use App\Models\Pelanggaran;
use App\Models\Artikel;
use App\Models\Sanksi;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\ExternalArticleMetadataService;
use App\Services\ImageCompressionService;
use App\Support\ActivityLogger;
use App\Support\Admin\AdminResourceExporter;
use App\Support\Admin\AdminRegistry;
use App\Support\HtmlSanitizer;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResourceController extends Controller
{
    protected function authorize(array $meta): void
    {
        $user = auth()->user();

        // Superadmin bisa akses semua
        if ($user->isSuperadmin()) {
            return;
        }

        // Resource "user" hanya untuk superadmin native — tidak boleh
        // diakses lewat additional_access agar tidak terjadi eskalasi
        // hak akses (admin bidang membuat user ber-role admin).
        if (($meta['slug'] ?? '') === 'user') {
            throw new AccessDeniedHttpException('Menu Pengguna Admin hanya dapat diakses oleh Admin.');
        }

        // Cek apakah user bisa akses group dari resource ini
        // (atau slug menu spesifik yang diberikan sebagai akses tambahan).
        if (! $user->canAccessResource($meta)) {
            throw new AccessDeniedHttpException('Anda tidak memiliki izin untuk mengakses menu ini. Silakan hubungi administrator.');
        }
    }

    /**
     * Pastikan resource memang mengizinkan pengubahan data.
     *
     * Beberapa menu merupakan arsip kegiatan yang hanya dapat ditambah dan
     * dilihat. Pemeriksaan ini berada di controller agar URL edit maupun PUT
     * langsung tetap ditolak, bukan hanya menyembunyikan tombol di antarmuka.
     */
    protected function ensureCanEdit(array $meta): void
    {
        abort_if(
            ($meta['can_edit'] ?? true) === false,
            403,
            'Menu ini hanya mendukung penambahan dan lihat detail. Pengubahan data tidak diizinkan.'
        );
    }

    /**
     * Administrator Utama tetap dapat meninjau, menambah, menghapus, dan
     * mengekspor data operasional, tetapi tidak boleh mengubah record yang
     * sudah ada. Konten & Sistem (Artikel serta Pengguna Admin) dikecualikan.
     *
     * Pemeriksaan ini sengaja berada di controller, bukan hanya Blade, agar
     * request PUT langsung tidak dapat melewati mode baca-saja di antarmuka.
     */
    protected function isReadOnlyForCurrentUser(array $meta): bool
    {
        $user = auth()->user();

        return $user?->isSuperadmin() === true
            && ($meta['group'] ?? null) !== 'konten';
    }

    protected function ensureCanUpdate(array $meta): void
    {
        $this->ensureCanEdit($meta);

        abort_if(
            $this->isReadOnlyForCurrentUser($meta),
            403,
            'Administrator Utama hanya dapat melihat data ini. Pengubahan data operasional dilakukan oleh admin bidang terkait.'
        );
    }

    protected function ensureCanCreate(array $meta): void
    {
        abort_if(
            ($meta['can_create'] ?? true) === false,
            403,
            'Menu ini hanya mendukung lihat detail dan edit. Penambahan data tidak diizinkan.'
        );

        abort_if(
            $this->isReadOnlyForCurrentUser($meta),
            403,
            'Administrator Utama hanya dapat melihat data ini. Penambahan data operasional dilakukan oleh admin bidang terkait.'
        );
    }

    public function index(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $this->query($meta, $request);

        if ($meta['slug'] === 'data-tpu') {
            $allTpus = $meta['model']::all();
            $totalTpu = $allTpus->count();
            $totalMakam = $allTpus->sum(fn ($t) => $t->totalMakam());
            $totalPohon = $allTpus->sum(fn ($t) => $t->totalPohon());

            return view('admin.data-tpu.index', [
                'resource' => $meta,
                'records' => $query->paginate(15)->withQueryString(),
                'search' => $request->string('q')->toString(),
                'sortColumn' => $request->string('sort')->toString(),
                'sortDirection' => $request->string('direction', 'asc')->toString(),
                'totalTpu' => $totalTpu,
                'totalMakam' => $totalMakam,
                'totalPohon' => $totalPohon,
            ]);
        }

        $view = match ($meta['slug']) {
            'artikel' => 'admin.artikel.index',
            default => 'admin.resources.index',
        };

        return view($view, [
            'resource' => $meta,
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $request->string('q')->toString(),
            'sortColumn' => $request->string('sort')->toString(),
            'sortDirection' => $request->string('direction', 'asc')->toString(),
        ]);
    }

    public function create(string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->ensureCanCreate($meta);

        $view = match ($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
            'artikel' => 'admin.artikel.form',
            'data-tpu' => 'admin.data-tpu.form',
            default => 'admin.resources.form',
        };

        return view($view, [
            'resource' => $meta,
            'record' => new $meta['model'],
            'fields' => AdminRegistry::formFields($meta),
            'method' => 'POST',
            'action' => route('admin.resources.store', $resource),
            'readOnly' => false,
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->ensureCanCreate($meta);
        $this->validateSpecialFields($request, $meta, false);
        $this->validateFromFields($request, $meta, false, null);

        if ($meta['slug'] === 'artikel' && $request->input('article_type', 'internal') === 'external') {
            return $this->storeExternalArtikel($request, $meta);
        }

        $record = new $meta['model'];
        if ($meta['slug'] === 'sosialisasi') {
            DB::transaction(function () use ($request, $meta, $record): void {
                $record->fill($this->payload($request, $meta, $record));
                $record->save();
                $this->storeDaftarHadir($request, $meta, $record);
            });
        } else {
            $record->fill($this->payload($request, $meta, $record));
            $record->save();
        }
        $this->storeSpecialRelations($request, $meta, $record);
        $this->storeSanksiIfPelanggaran($request, $record);

        // Handle role assignment untuk user — hanya superadmin yang boleh
        // mengubah role & additional_access (defense-in-depth di atas authorize()).
        if ($resource === 'user') {
            abort_unless(auth()->user()->isSuperadmin(), 403, 'Hanya Admin yang dapat mengelola role pengguna.');

            if ($request->filled('role')) {
                $record->syncRoles([$request->input('role')]);
            }

            // Handle additional_access untuk user
            if ($request->has('additional_access')) {
                $record->additional_access = $request->input('additional_access', []);
                $record->save();
            }
        }

        // Simpan foto profil user (bila diunggah) — otomatis dikompres & dikonversi ke WebP.
        if ($resource === 'user' && $request->hasFile('photo')) {
            $photoPath = app(FileUploadService::class)->store($request->file('photo'), 'avatars', 'public');

            if ($photoPath !== false) {
                $record->photo_path = $photoPath;
                $record->save();
            }
        }

        return redirect()->route('admin.resources.show', [$resource, $record])->with('success', $meta['label'].' berhasil ditambahkan.');
    }

    public function show(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $model = $meta['model']::findOrFail($record);

        $view = match ($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.show',
            'artikel' => 'admin.artikel.show',
            'data-tpu' => 'admin.data-tpu.show',
            default => 'admin.resources.show',
        };

        return view($view, [
            'resource' => $meta,
            'record' => $model,
            'fields' => AdminRegistry::formFields($meta),
        ]);
    }

    public function edit(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->ensureCanEdit($meta);
        $model = $meta['model']::findOrFail($record);

        $view = match ($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
            'artikel' => 'admin.artikel.form',
            'data-tpu' => 'admin.data-tpu.form',
            default => 'admin.resources.form',
        };

        return view($view, [
            'resource' => $meta,
            'record' => $model,
            'fields' => AdminRegistry::formFields($meta),
            'method' => 'PUT',
            'action' => route('admin.resources.update', [$resource, $model]),
            'readOnly' => $this->isReadOnlyForCurrentUser($meta),
        ]);
    }

    public function update(Request $request, string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->ensureCanUpdate($meta);
        $this->validateSpecialFields($request, $meta, true);

        $model = $meta['model']::findOrFail($record);
        $this->validateFromFields($request, $meta, true, $model);

        if ($meta['slug'] === 'artikel' && $model instanceof Artikel && $model->isExternal()) {
            return $this->updateExternalArtikel($request, $meta, $model);
        }

        if ($meta['slug'] === 'sosialisasi') {
            DB::transaction(function () use ($request, $meta, $model): void {
                $model->fill($this->payload($request, $meta, $model));
                $model->save();
                $this->storeDaftarHadir($request, $meta, $model);
            });
        } else {
            $model->fill($this->payload($request, $meta, $model));
            $model->save();
        }
        $this->storeSpecialRelations($request, $meta, $model);
        $this->storeSanksiIfPelanggaran($request, $model);

        // Handle role assignment untuk user — hanya superadmin yang boleh
        // mengubah role & additional_access (defense-in-depth di atas authorize()).
        if ($resource === 'user') {
            abort_unless(auth()->user()->isSuperadmin(), 403, 'Hanya Admin yang dapat mengelola role pengguna.');

            if ($request->filled('role')) {
                // Jangan update role jika user target adalah superadmin (protection)
                if (! $model->isSuperadmin()) {
                    $model->syncRoles([$request->input('role')]);
                }
            }

            // Handle additional_access untuk user
            if ($request->has('additional_access')) {
                $model->additional_access = $request->input('additional_access', []);
                $model->save();
            }
        }

        // Foto profil: hapus bila diminta, atau ganti bila ada file baru.
        if ($resource === 'user') {
            if ($request->boolean('photo_remove') && $model->photo_path) {
                // Lewat service terpusat agar versi lama di B2 ikut ter-purge.
                app(FileUploadService::class)->deletePath($model->photo_path);
                $model->photo_path = null;
                $model->save();
            } elseif ($request->hasFile('photo')) {
                $photoPath = app(FileUploadService::class)->store($request->file('photo'), 'avatars', 'public');

                if ($photoPath !== false) {
                    // Hapus foto lama hanya setelah file baru sukses tersimpan.
                    app(FileUploadService::class)->deletePath($model->photo_path);
                    $model->photo_path = $photoPath;
                    $model->save();
                }
            }
        }

        return redirect()->route('admin.resources.show', [$resource, $model])->with('success', $meta['label'].' berhasil diperbarui.');
    }

    /**
     * Reset password pengguna secara langsung (tanpa memerlukan password saat ini).
     * Hanya boleh dilakukan oleh superadmin — berguna bila admin lupa password
     * sehingga tidak terkunci keluar dari panel.
     */
    public function resetPassword(Request $request, int|string $record)
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403, 'Hanya Admin yang dapat mereset password pengguna.');

        $target = User::findOrFail($record);

        $validated = $request->validate([
            'password' => ['required', 'string', Password::defaults()],
        ], [
            'password.required' => 'Password baru wajib diisi.',
        ]);

        $target->password = Hash::make($validated['password']);
        $target->save();

        return redirect()
            ->route('admin.resources.show', ['user', $target])
            ->with('success', 'Password untuk '.$target->name.' berhasil direset. Sampaikan password baru melalui kanal yang aman.');
    }

    /**
     * Unduh dokumen/lampiran (field file maupun relasi dokumen) dari storage publik.
     *
     * Pengakses wajib menyertakan parameter `resource` (slug AdminRegistry):
     * akses dicek lewat authorize() per-bidang (pola sama dengan destroy()),
     * lalu path dibatasi hanya boleh berada di direktori penyimpanan resource
     * tersebut ('admin/{slug}/' untuk field file langsung, atau direktori
     * relasi dari AdminRegistry::relationUploads). Path juga divalidasi agar
     * tetap berada di dalam direktori storage/app/public sehingga tidak bisa
     * digunakan untuk mengakses file di luar storage publik.
     */
    public function downloadFile(Request $request)
    {
        $path = (string) $request->query('path', '');
        $name = (string) $request->query('name', '');
        $resource = (string) $request->query('resource', '');

        // Otorisasi per-bidang: resource wajib disertakan dan harus dikenal.
        abort_unless($resource !== '', 403, 'Akses file ditolak.');
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);

        // Validasi path: tolak kosong, traversal (..), path absolut,
        // drive letter Windows, null byte, dan backslash.
        abort_unless(
            $path !== ''
            && ! str_contains($path, '..')
            && ! str_starts_with($path, '/')
            && ! str_starts_with($path, '\\')
            && ! str_contains($path, ':')
            && ! str_contains($path, "\0")
            && ! str_contains($path, '\\'),
            403,
            'Akses file ditolak.'
        );

        abort_unless(AdminRegistry::isAllowedFilePath($path, $meta['slug']), 403, 'Akses file ditolak.');

        try {
            $exists = Storage::disk('public')->exists($path);
        } catch (\Throwable $e) {
            // Penyimpanan sedang tidak dapat diakses — catat di log dan
            // tampilkan halaman ramah, bukan error teknis.
            report($e);
            abort(404, 'File tidak dapat diakses saat ini. Silakan coba beberapa saat lagi.');
        }

        abort_unless($exists, 404, 'File tidak ditemukan.');

        // Nama unduhan: gunakan nama yang dikirim (lebih mudah dibaca), lalu
        // pastikan ekstensi sesuai dengan file asli di storage agar format
        // (pdf, docx, xlsx, jpg, png, dll) tidak hilang saat disimpan.
        $downloadName = basename($name) ?: basename($path);

        $pathExt = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $nameExt = strtolower((string) pathinfo($downloadName, PATHINFO_EXTENSION));

        if ($pathExt !== '') {
            if ($nameExt === '' || $nameExt === 'file') {
                // Belum ada ekstensi (atau ekstensi generik ".file") -> gunakan ekstensi asli.
                $base = $nameExt === 'file' ? pathinfo($downloadName, PATHINFO_FILENAME) : $downloadName;
                $downloadName = $base.'.'.$pathExt;
            } elseif ($nameExt !== $pathExt) {
                // Ekstensi tidak cocok dengan file asli -> ganti agar sesuai format.
                $downloadName = pathinfo($downloadName, PATHINFO_FILENAME).'.'.$pathExt;
            }
        }

        try {
            return Storage::disk('public')->download($path, $downloadName);
        } catch (\Throwable $e) {
            report($e);
            abort(404, 'File tidak dapat diakses saat ini. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Preview inline dokumen/lampiran dari storage publik via proxy web lokal.
     *
     * Tidak mengembalikan URL signed B2, melainkan menjembatani file dari B2
     * lalu menyajikannya inline (Content-Disposition: inline) sehingga address
     * bar menampilkan domain web sendiri, bukan URL storage. Akses wajib login
     * admin (middleware di web.php) dan diotorisasi per-bidang seperti download.
     */
    public function previewFile(Request $request)
    {
        $resource = (string) $request->route('resource');
        $file = (string) $request->route('file');

        // Otorisasi: resource wajib dikenal (find() akan 404 bila tak dikenal).
        $meta = AdminRegistry::find($resource);

        // data-tpu merupakan resource publik (dokumentasi foto pemakaman umum) sehingga dapat diakses publik
        if ($resource !== 'data-tpu') {
            abort_unless(auth()->check() && auth()->user()->hasAdminAccess(), 403, 'Akses file ditolak. Silakan masuk terlebih dahulu.');
            $this->authorize($meta);
        }

        // Validasi path: tolak kosong, traversal (..), path absolut,
        // drive letter Windows, null byte, dan backslash.
        abort_unless(
            $file !== ''
            && ! str_contains($file, '..')
            && ! str_starts_with($file, '/')
            && ! str_starts_with($file, '\\')
            && ! str_contains($file, ':')
            && ! str_contains($file, "\0")
            && ! str_contains($file, '\\'),
            403,
            'Akses file ditolak.'
        );

        // URL hanya membawa basename (tanpa subdirektori). Resolusi ke path
        // lengkap: (1) coba prefix yang dikenal, lalu (2) cari berdasar
        // basename di seluruh direktori resource (ter-scope per resource).
        $slug = $meta['slug'];
        $basename = basename($file);
        $candidate = null;

        $candidate = null;

        try {
            $prefixes = array_map(fn (string $directory) => $directory.'/', AdminRegistry::fileDirectories($slug));

            foreach ($prefixes as $prefix) {
                if (Storage::disk('public')->exists($prefix.$basename)) {
                    $candidate = $prefix.$basename;
                    break;
                }
            }

            if ($candidate === null) {
                foreach (AdminRegistry::fileDirectories($slug) as $dir) {
                    foreach (Storage::disk('public')->allFiles($dir) as $key) {
                        if (basename($key) === $basename) {
                            $candidate = $key;
                            break 2;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Penyimpanan sedang tidak dapat diakses — jangan bocorkan error
            // teknis ke pengguna; catat di log dan tampilkan halaman ramah.
            report($e);
            abort(404, 'File tidak dapat diakses saat ini. Silakan coba beberapa saat lagi.');
        }

        abort_unless($candidate !== null, 404, 'File tidak ditemukan.');

        try {
            return Storage::disk('public')->response($candidate, basename($candidate));
        } catch (\Throwable $e) {
            report($e);
            abort(404, 'File tidak dapat diakses saat ini. Silakan coba beberapa saat lagi.');
        }
    }

    public function destroy(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $model = $meta['model']::findOrFail($record);

        // Hapus file di storage terlebih dahulu (sebelum cascade delete di DB).
        $this->deleteRecordFiles($meta, $model);

        $model->delete();

        return redirect()->route('admin.resources.index', $resource)->with('success', $meta['label'].' berhasil dihapus.');
    }

    /**
     * Hapus satu file lampiran/relasi (mis. SosialisasiFile) dari record.
     * File fisik di storage dihapus dan baris relasi di database dibersihkan.
     */
    public function destroyRelationFile(Request $request, string $resource, int|string $record, string $relation, int|string $fileId)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->ensureCanUpdate($meta);

        $model = $meta['model']::findOrFail($record);

        $uploadConfig = collect(AdminRegistry::relationUploads($meta['slug']))
            ->first(fn ($u) => ($u['relation'] ?? null) === $relation || ($u['name'] ?? null) === $relation);

        abort_unless($uploadConfig, 404, 'Konfigurasi relasi lampiran tidak ditemukan.');

        $fileModelClass = $uploadConfig['model'];
        $foreignKey = $uploadConfig['foreign_key'];
        $pathField = $uploadConfig['path_field'] ?? 'path';

        $fileItem = $fileModelClass::where($foreignKey, $model->getKey())
            ->where('id', $fileId)
            ->firstOrFail();

        $filePath = $fileItem->{$pathField} ?? null;

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        $fileItem->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function export(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);

        // Task 12: saat antrean aktif, jangan blokir request — buat file via job.
        if (config('exports.queue')) {
            GenerateExportJob::dispatch(
                userId: auth()->id(),
                slug: $resource,
                scope: 'filter',
                format: $request->string('format', 'xlsx')->toString(),
                filters: $request->query(),
            );

            return back()->with('info', 'Ekspor (.xlsx) sedang dibuat di antrean. Notifikasi akan muncul saat file siap diunduh.');
        }

        $query = $this->query($meta, $request);

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'filter');
    }

    public function exportAll(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);

        if (config('exports.queue')) {
            GenerateExportJob::dispatch(
                userId: auth()->id(),
                slug: $resource,
                scope: 'all',
                format: $request->string('format', 'xlsx')->toString(),
            );

            return back()->with('info', 'Ekspor "Semua Data" dijadwalkan di antrean. Notifikasi akan muncul saat file siap diunduh.');
        }

        $query = $meta['model']::query()->orderByDesc((new $meta['model'])->getKeyName());

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'all');
    }

    public function bulkExport(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $ids = $request->input('ids', []);

        if (config('exports.queue')) {
            GenerateExportJob::dispatch(
                userId: auth()->id(),
                slug: $resource,
                scope: 'bulk',
                format: $request->string('format', 'xlsx')->toString(),
                ids: (array) $ids,
            );

            return back()->with('info', 'Ekspor '.count($ids).' data terpilih dijadwalkan di antrean. Notifikasi akan muncul saat file siap diunduh.');
        }

        $query = $meta['model']::query()->whereIn('id', $ids);

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'bulk');
    }

    /**
     * Task 12 — unduh file ekspor berantre (privasi: hanya user pemilik notifikasi).
     */
    public function downloadExport(Request $request, string $token)
    {
        $notification = auth()->user()->notifications()
            ->get()
            ->first(fn ($n) => is_string($n->data['href'] ?? null) && str_ends_with($n->data['href'], '/'.$token));

        abort_if(! $notification, 404, 'File ekspor tidak ditemukan atau kadaluwarsa.');

        $dir = storage_path('app/private/'.trim(config('exports.storage_dir', 'exports'), '/'));
        $file = $dir.'/'.$token;

        abort_if(! is_file($file), 404, 'File ekspor sudah diunduh atau dihapus.');

        $downloadName = $notification->data['download_name'] ?? $token;
        $notification->markAsRead();

        return response()->download($file, $downloadName)->deleteFileAfterSend(true);
    }

    /**
     * Unduh data resource dalam format xlsx | csv.
     */
    protected function downloadData(array $meta, $query, string $format, string $scope)
    {
        $format = in_array($format, ['xlsx', 'csv'], true) ? $format : 'xlsx';
        $label = \App\Support\DataIO::sanitizeFilename($meta['label'] ?? 'Data');
        $filename = $label.' - '.now()->format('Y-m-d - H.i.s');

        ActivityLogger::log('exported', $meta['label'].' ('.strtoupper($format).', '.$scope.')', $meta['slug']);

        $exporter = app(AdminResourceExporter::class);

        if ($format === 'csv') {
            return $exporter->csvDownload($query, $meta, $filename.'.csv');
        }

        $tmpPath = storage_path('app/private/'.\Illuminate\Support\Str::uuid().'.xlsx');
        $exporter->write($query, $meta, 'xlsx', $tmpPath);

        return response()->download($tmpPath, $filename.'.xlsx')->deleteFileAfterSend(true);
    }

    public function bulkDelete(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $ids = $request->input('ids', []);

        $model = $meta['model'];
        $deleted = 0;
        foreach ($ids as $id) {
            $record = $model::find($id);
            if ($record) {
                $this->deleteRecordFiles($meta, $record);
                $record->delete();
                $deleted++;
            }
        }

        return redirect()->route('admin.resources.index', $resource)
            ->with('success', $deleted.' '.$meta['label'].' berhasil dihapus.');
    }

    /**
     * Hapus semua file milik record dari storage SEBELUM record dihapus dari
     * database. Path file pada relasi (foto, dokumen, media) harus dibaca
     * terlebih dahulu karena baris relasi ikut terhapus via cascadeOnDelete.
     *
     * Kegagalan penghapusan file diabaikan agar tidak pernah menggagalkan
     * penghapusan record itu sendiri.
     */
    protected function deleteRecordFiles(array $meta, Model $record): void
    {
        try {
            $paths = [];
            $stagingPaths = [];

            // 1. Field file langsung dari form (thumbnail, surat_permohonan, dll).
            foreach (AdminRegistry::formFields($meta) as $field) {
                if (($field['type'] ?? null) !== 'file') {
                    continue;
                }

                $value = $record->{$field['name']} ?? null;

                if (is_string($value) && filled($value)) {
                    $paths[] = $value;
                } elseif (is_array($value)) {
                    foreach ($value as $item) {
                        if (is_string($item) && filled($item)) {
                            $paths[] = $item;
                        }
                    }
                }
            }

            // 1b. Data TPU: foto_dokumentasi array
            if ($meta['slug'] === 'data-tpu' && is_array($record->foto_dokumentasi)) {
                foreach ($record->foto_dokumentasi as $p) {
                    if (is_string($p) && filled($p)) {
                        $paths[] = $p;
                    }
                }
            }

            // 2. Foto profil user.
            if ($meta['slug'] === 'user' && filled($record->photo_path ?? null)) {
                $paths[] = $record->photo_path;
            }

            // 3. File dari relasi upload (foto pengaduan, dokumen, media, file sosialisasi).
            foreach (AdminRegistry::relationUploads($meta['slug']) as $upload) {
                $relation = $upload['relation'] ?? null;

                if (! $relation || ! method_exists($record, $relation)) {
                    continue;
                }

                foreach ($record->{$relation} as $row) {
                    $pathField = $upload['path_field'] ?? null;

                    if ($pathField && filled($row->{$pathField} ?? null)) {
                        $paths[] = $row->{$pathField};
                    }

                    // File staging di disk lokal (foto yang masih antre diproses).
                    if (filled($row->staging_path ?? null)) {
                        $stagingPaths[] = $row->staging_path;
                    }
                }
            }

            // 3b. Sosialisasi: sertifikat peserta (bila pernah diunggah).
            if ($meta['slug'] === 'sosialisasi' && method_exists($record, 'pesertas')) {
                foreach ($record->pesertas as $peserta) {
                    if (filled($peserta->sertifikat_path ?? null)) {
                        $paths[] = $peserta->sertifikat_path;
                    }
                }
            }

            // 4. Pelanggaran: surat sanksi ikut dihapus.
            if ($meta['slug'] === 'pelanggaran' && filled($record->sanksi?->surat_path ?? null)) {
                $paths[] = $record->sanksi->surat_path;
            }

            // 5. Artikel: gambar yang disematkan di konten (Jodit) ikut dibersihkan,
            //    kecuali gambar tersebut masih dipakai oleh artikel lain.
            if (in_array($meta['slug'], ['artikel', 'artikel-pengendalian', 'artikel-sampah-lb3', 'artikel-tata-penataan', 'artikel-rth'], true)) {
                $konten = (string) ($record->konten ?? '');

                if ($konten !== '' && preg_match_all('~artikel-images(?:/|%2F)[^"\'\s<>?&]+~', $konten, $matches)) {
                    $embedded = array_unique(array_map('urldecode', $matches[0]));

                    // Ekstrak path gambar dari semua artikel lain (regex yang sama)
                    // lalu bandingkan path yang sudah di-decode, agar tahan terhadap
                    // perbedaan encoding URL (spasi, unicode, dll).
                    $otherPaths = [];

                    $otherKonten = $meta['model']::query()
                        ->whereKeyNot($record->getKey())
                        ->whereNotNull('konten')
                        ->pluck('konten')
                        ->all();

                    foreach ($otherKonten as $other) {
                        if (preg_match_all('~artikel-images(?:/|%2F)[^"\'\s<>?&]+~', (string) $other, $otherMatches)) {
                            foreach (array_map('urldecode', $otherMatches[0]) as $op) {
                                $otherPaths[] = $op;
                            }
                        }
                    }

                    $otherPaths = array_unique($otherPaths);

                    foreach ($embedded as $path) {
                        // Jangan hapus gambar yang masih dipakai artikel lain.
                        if (! in_array($path, $otherPaths, true)) {
                            $paths[] = $path;
                        }
                    }
                }
            }

            $files = app(FileUploadService::class);
            $files->deletePaths(array_unique(array_filter($paths)));

            if ($stagingPaths !== []) {
                $files->deletePaths(array_unique(array_filter($stagingPaths)), 'local');
            }
        } catch (\Throwable $e) {
            // Pembersihan file tidak boleh menggagalkan penghapusan record.
        }
    }

    protected function query(array $meta, Request $request)
    {
        $model = new $meta['model'];
        $query = $meta['model']::query();

        // Filtering generik berdasarkan definisi $meta['filters'].
        foreach (($meta['filters'] ?? []) as $filter) {
            $key = $filter['key'] ?? null;
            $type = $filter['type'] ?? null;
            $column = $filter['column'] ?? null;

            if (! $key || ! $type || ! $column) {
                continue;
            }

            if ($type === 'daterange') {
                // Terima {key}_from/{key}_to, kompatibel dengan date_from/date_to lama.
                $from = $request->input($key.'_from') ?? $request->input('date_from');
                $to = $request->input($key.'_to') ?? $request->input('date_to');

                if (filled($from)) {
                    $query->whereDate($column, '>=', $from);
                }
                if (filled($to)) {
                    $query->whereDate($column, '<=', $to);
                }

                continue;
            }

            if (in_array($type, ['multiselect', 'select'], true)) {
                $value = $request->input($key);
                $values = is_array($value)
                    ? array_filter($value)
                    : (filled($value) ? [$value] : []);

                if (empty($values)) {
                    continue;
                }

                // Penanganan khusus: role user disimpan di tabel relation (spatie).
                if ($column === 'role' && method_exists($meta['model'], 'roles')) {
                    $query->whereHas('roles', fn ($q) => $q->whereIn('name', $values));

                    continue;
                }

                $query->whereIn($column, $values);
            }
        }

        // Sorting
        $sortColumn = $request->string('sort')->toString();
        $sortDirection = $request->string('direction', 'asc')->toString();

        // Columns that are not direct DB columns (virtual/computed)
        $virtualColumns = ['role'];

        if ($sortColumn && in_array($sortColumn, $meta['columns']) && ! in_array($sortColumn, $virtualColumns)) {
            $query->orderBy($sortColumn, in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc');
        } else {
            $query->orderByDesc($model->getKeyName());
        }

        // Eager load relationships for specific resources — cegah N+1 di tabel index.
        if ($meta['slug'] === 'pelanggaran') {
            // Kolom index jenis_sanksi_text / status_sanksi_text memakai relasi sanksi.
            $query->with('sanksi');
        } elseif ($meta['slug'] === 'user') {
            // Kolom 'role' dan nama peran di index memakai $record->roles->first().
            $query->with('roles');
        } elseif ($meta['slug'] === 'artikel') {
            // Kolom penulis dan badge komentar memakai eager load/agregat tunggal,
            // bukan query tambahan dari Blade untuk setiap baris.
            $query->with('user')->withCount('komentars');
        }

        // Search
        $search = trim($request->string('q')->toString());

        if ($search !== '') {
            $columns = collect(array_merge($meta['columns'], $model->getFillable()))
                ->unique()
                ->reject(fn ($column) => Str::endsWith($column, '_id') || in_array($column, ['password', 'remember_token', 'email_verified_at', 'additional_access', 'photo_path', 'preferences', 'role']))
                ->take(8)
                ->values();

            $query->where(function ($builder) use ($columns, $search) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return $query;
    }

    protected function payload(Request $request, array $meta, Model $record): array
    {
        $payload = [];

        if ($meta['slug'] === 'data-tpu') {
            $payload['nama_tpu'] = (string) $request->input('nama_tpu');
            $payload['luas_area_makam'] = (string) $request->input('luas_area_makam');

            $rawVegetasi = $request->input('vegetasi', []);
            if (is_string($rawVegetasi)) {
                $rawVegetasi = json_decode($rawVegetasi, true) ?: [];
            }
            $vegetasi = [];
            if (is_array($rawVegetasi)) {
                foreach ($rawVegetasi as $v) {
                    if (is_array($v) && filled($v['jenis_pohon'] ?? null)) {
                        $vegetasi[] = [
                            'jenis_pohon' => trim((string) $v['jenis_pohon']),
                            'jumlah' => trim((string) ($v['jumlah'] ?? '')),
                        ];
                    }
                }
            }
            $payload['vegetasi'] = $vegetasi;

            $rawBlok = $request->input('kapasitas_blok', []);
            if (is_string($rawBlok)) {
                $rawBlok = json_decode($rawBlok, true) ?: [];
            }
            $kapasitasBlok = [];
            if (is_array($rawBlok)) {
                foreach ($rawBlok as $b) {
                    if (is_array($b) && filled($b['agama'] ?? null)) {
                        $kapasitasBlok[] = [
                            'agama' => trim((string) $b['agama']),
                            'jumlah_blok' => trim((string) ($b['jumlah_blok'] ?? '')),
                            'kapasitas_per_blok' => trim((string) ($b['kapasitas_per_blok'] ?? '')),
                            'jumlah_makam' => trim((string) ($b['jumlah_makam'] ?? '')),
                            'makam_terisi' => trim((string) ($b['makam_terisi'] ?? '')),
                            'makam_kosong' => trim((string) ($b['makam_kosong'] ?? '')),
                        ];
                    }
                }
            }
            $payload['kapasitas_blok'] = $kapasitasBlok;

            // Handle Dynamic Foto Dokumentasi (0, 1, 2, atau lebih)
            $existingPhotos = $request->input('existing_photos', []);
            if (is_string($existingPhotos)) {
                $existingPhotos = json_decode($existingPhotos, true) ?: [];
            }
            $existingPhotos = is_array($existingPhotos) ? array_values(array_filter($existingPhotos, fn ($p) => is_string($p) && filled($p))) : [];

            // Hapus file foto lama yang dihapus oleh pengguna
            if ($record->exists) {
                $oldPhotos = is_array($record->foto_dokumentasi) ? $record->foto_dokumentasi : [];
                for ($i = 1; $i <= 3; $i++) {
                    if (filled($record->{'foto_dokumentasi_'.$i})) {
                        $oldPhotos[] = $record->{'foto_dokumentasi_'.$i};
                    }
                }
                $oldPhotos = array_unique($oldPhotos);
                foreach ($oldPhotos as $oldP) {
                    if (! in_array($oldP, $existingPhotos, true)) {
                        app(FileUploadService::class)->deletePath($oldP);
                    }
                }
            }

            $finalPhotos = $existingPhotos;

            // Unggah foto baru dari new_photos[]
            if ($request->hasFile('new_photos')) {
                $newFiles = $request->file('new_photos');
                if (! is_array($newFiles)) {
                    $newFiles = [$newFiles];
                }
                foreach ($newFiles as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        $stored = app(FileUploadService::class)->store($file, 'admin/data-tpu', 'public');
                        if ($stored !== false) {
                            $finalPhotos[] = $stored;
                        }
                    }
                }
            }

            // Dukungan foto langsung foto_dokumentasi / legacy single upload
            if ($request->hasFile('foto_dokumentasi')) {
                $directFiles = $request->file('foto_dokumentasi');
                if (! is_array($directFiles)) {
                    $directFiles = [$directFiles];
                }
                foreach ($directFiles as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        $stored = app(FileUploadService::class)->store($file, 'admin/data-tpu', 'public');
                        if ($stored !== false) {
                            $finalPhotos[] = $stored;
                        }
                    }
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                $photoField = 'foto_dokumentasi_'.$i;
                if ($request->hasFile($photoField)) {
                    $file = $request->file($photoField);
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        $stored = app(FileUploadService::class)->store($file, 'admin/data-tpu', 'public');
                        if ($stored !== false) {
                            $finalPhotos[] = $stored;
                        }
                    }
                }
            }

            $payload['foto_dokumentasi'] = array_values(array_unique($finalPhotos));

            return $payload;
        }

        foreach (AdminRegistry::formFields($meta) as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';

            if (in_array($type, ['section', 'photos', 'relation_files', 'daftar_hadir'], true)) {
                continue;
            }

            if ($type === 'file') {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    if ($this->fileMatchesAccept($file, $field['accept'] ?? null)) {
                        // Gambar raster otomatis dikompres & dikonversi ke WebP;
                        // file lain (pdf, doc, dll) disimpan apa adanya.
                        $stored = app(FileUploadService::class)->store($file, 'admin/'.$meta['slug'], 'public');

                        if ($stored !== false) {
                            // Hapus file lama bila file diganti saat edit.
                            if ($record->exists) {
                                $old = $record->getOriginal($name);
                                $oldPaths = is_array($old) ? $old : [$old];

                                foreach ($oldPaths as $oldPath) {
                                    if (is_string($oldPath) && filled($oldPath) && $oldPath !== $stored) {
                                        app(FileUploadService::class)->deletePath($oldPath);
                                    }
                                }
                            }

                            $payload[$name] = $stored;
                        }
                    }
                }

                continue;
            }

            if ($type === 'checkbox') {
                $payload[$name] = $request->boolean($name);

                continue;
            }

            if ($name === 'password') {
                if (filled($request->input($name))) {
                    $payload[$name] = Hash::make($request->input($name));
                }

                continue;
            }

            // Konten artikel dirender apa adanya ({!! !!}) di halaman publik,
            // jadi wajib disanitasi agar markup berbahaya (script, on*, dsb)
            // tidak tersimpan sebagai stored XSS.
            if ($name === 'konten' && str_starts_with($meta['slug'], 'artikel')) {
                $payload[$name] = HtmlSanitizer::clean($request->input($name));

                continue;
            }

            // Beberapa relasi opsional (mis. Sidak pada Pelanggaran) boleh
            // ditulis manual melalui opsi “Lainnya”. Nilai sentinel tidak
            // pernah disimpan ke foreign key; relasi dikosongkan dan teks
            // manual disimpan di kolom yang dinyatakan oleh resource.
            if ($type === 'select' && filled($field['manual_field'] ?? null) && $request->exists($name)) {
                $manualField = (string) $field['manual_field'];
                $selected = $request->input($name);

                if ($selected === '__lainnya__') {
                    $payload[$name] = null;
                    $payload[$manualField] = filled($request->input($manualField))
                        ? trim((string) $request->input($manualField))
                        : null;

                    continue;
                }

                $payload[$name] = $selected === '' ? null : $selected;
                $payload[$manualField] = null;

                continue;
            }

            if ($request->exists($name)) {
                $payload[$name] = $request->input($name) === '' ? null : $request->input($name);
            }
        }

        // Toggle "Izinkan Komentar" dirender khusus oleh form artikel
        // (bukan bagian dari definisi field generik AdminRegistry), sehingga
        // nilainya disimpan manual agar status on/off benar-benar terpersist.
        if (str_starts_with((string) ($meta['slug'] ?? ''), 'artikel') && $request->exists('komentar_enabled')) {
            $payload['komentar_enabled'] = $request->boolean('komentar_enabled');
        }

        return $payload;
    }

    /**
     * Cek apakah file yang diunggah cocok dengan atribut `accept`.
     * Mendukung mime type (image/jpeg), wildcard (image/*), serta
     * ekstensi bertitik (.pdf) maupun polos (jpg). Bersikap permisif:
     * jika accept kosong / tidak bisa diurai, file tetap diterima.
     */
    protected function fileMatchesAccept(UploadedFile $file, ?string $accept): bool
    {
        $accept = trim((string) $accept);
        if ($accept === '') {
            return true;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());

        // Peta mime -> ekstensi umum untuk mencocokkan token accept.
        $mimeExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/jpg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/avif' => ['avif'],
            'image/heic' => ['heic'],
            'image/heif' => ['heif'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        ];

        foreach (explode(',', $accept) as $token) {
            $token = strtolower(trim($token));
            if ($token === '') {
                continue;
            }

            // Wildcard mime seperti image/* atau video/*
            if (str_ends_with($token, '/*')) {
                $prefix = substr($token, 0, -1); // "image/"
                if (str_starts_with($mime, $prefix)) {
                    return true;
                }
                if (str_starts_with($token, 'image/') && in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif', 'heic', 'heif'], true)) {
                    return true;
                }

                continue;
            }

            // Mime type penuh seperti image/jpeg
            if (str_contains($token, '/')) {
                if ($token === $mime) {
                    return true;
                }
                if (in_array($ext, $mimeExtensions[$token] ?? [], true)) {
                    return true;
                }

                continue;
            }

            // Ekstensi bertitik (.pdf) atau polos (pdf)
            $tokenExt = ltrim($token, '.');
            if ($tokenExt !== '' && $tokenExt === $ext) {
                return true;
            }
        }

        return false;
    }

    protected function validateSpecialFields(Request $request, array $meta, bool $updating): void
    {
        // Validasi foto profil untuk resource 'user' (tambah / ubah / hapus).
        if ($meta['slug'] === 'user') {
            $request->validate([
                'photo' => ['nullable', 'mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
                'photo_remove' => ['nullable', 'boolean'],
            ], [
                'photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
                'photo.max' => 'Ukuran foto profil maksimal 5MB.',
            ]);

            return;
        }

        // Daftar hadir Monitoring & Evaluasi dikirim sebagai array per baris.
        // Validasi struktur di sini agar ID peserta tidak dapat dipalsukan dan
        // setiap nilai yang akan disimpan selalu sesuai tipe kolomnya.
        if ($meta['slug'] === 'sosialisasi' && $request->input('jenis_kegiatan') === 'monitoring-evaluasi') {
            $request->validate([
                'daftar_hadir' => ['nullable', 'array'],
                'daftar_hadir.*' => ['array'],
                'daftar_hadir.*.id' => ['nullable', 'integer', 'min:1'],
                'daftar_hadir.*.nama_perusahaan' => ['nullable', 'string', 'max:255'],
                'daftar_hadir.*.jenis_usaha' => ['nullable', 'string', 'max:255'],
                'daftar_hadir.*.tanggal' => ['nullable', 'date'],
                'daftar_hadir.*.lokasi' => ['nullable', 'string', 'max:255'],
                'daftar_hadir.*.tim_survey' => ['nullable', 'string', 'max:255'],
            ]);

            return;
        }

        if ($meta['slug'] === 'data-tpu') {
            $request->validate([
                'nama_tpu' => ['required', 'string', 'max:255'],
                'luas_area_makam' => ['required', 'string', 'max:100'],
                'vegetasi' => ['nullable', 'array'],
                'kapasitas_blok' => ['nullable', 'array'],
                'existing_photos' => ['nullable', 'array'],
                'new_photos' => ['nullable', 'array'],
                'new_photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
                'foto_dokumentasi' => ['nullable'],
                'foto_dokumentasi.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
                'foto_dokumentasi_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
                'foto_dokumentasi_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
                'foto_dokumentasi_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            ], [
                'nama_tpu.required' => 'Nama TPU wajib diisi.',
                'luas_area_makam.required' => 'Luas area makam wajib diisi.',
                'new_photos.*.image' => 'File foto dokumentasi harus berupa gambar.',
                'new_photos.*.max' => 'Ukuran foto maksimal 5MB.',
                'foto_dokumentasi.*.image' => 'File foto dokumentasi harus berupa gambar.',
            ]);

            return;
        }

        $pengaduanSlugs = ['pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth'];

        if (! in_array($meta['slug'], $pengaduanSlugs)) {
            return;
        }

        $jenisOptions = match ($meta['slug']) {
            'pengaduan-pengendalian' => JenisPengaduanPengendalian::options(),
            'pengaduan-sampah' => JenisPengaduanSampah::options(),
            'pengaduan-rth' => JenisPengaduanRth::options(),
        };

        $statusOptions = StatusPengaduan::options();

        $status = $request->input('status');
        $isDitolak = false;

        $request->validate([
            'nama_pelapor' => [$updating ? 'nullable' : 'required', 'string', 'max:255'],
            'nomor_hp' => [$updating ? 'nullable' : 'required', 'string', 'max:30'],
            'jenis_pengaduan' => $updating ? ['nullable'] : ['required', Rule::in(array_keys($jenisOptions))],
            'alamat' => [$updating ? 'nullable' : 'required', 'string'],
            'deskripsi' => [$updating ? 'nullable' : 'required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(array_keys($statusOptions))],
            'catatan_admin' => ['nullable', 'string'],
            'alasan_penolakan' => ['nullable', 'string'],
            'photos' => [$updating ? 'nullable' : 'required', 'array', 'max:5'],
            'photos.*' => ['mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
        ], [
            'nama_pelapor.required' => 'Nama lengkap wajib diisi.',
            'nomor_hp.required' => 'Nomor telepon wajib diisi.',
            'jenis_pengaduan.required' => 'Jenis pengaduan wajib dipilih.',
            'jenis_pengaduan.in' => 'Jenis pengaduan tidak valid.',
            'alamat.required' => 'Lokasi kejadian wajib diisi.',
            'deskripsi.required' => 'Deskripsi pengaduan wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'status.required' => 'Status pengaduan wajib dipilih.',
            'status.in' => 'Status pengaduan tidak valid.',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'photos.required' => 'Foto bukti wajib diunggah.',
            'photos.max' => 'Maksimal 5 foto bukti.',
            'photos.*.mimes' => 'Foto bukti harus berformat JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 5MB.',
        ]);
    }

    /**
     * Validasi server generik untuk semua resource.
     * - Field bertanda required pada definisi resource wajib diisi.
     * - Bila tidak ditandai, kewajiban diambil dari skema DB (kolom NOT NULL
     *   tanpa default) agar submit kosong menghasilkan 422, bukan 500.
     * - Saat update, field readonly_on_edit dikunci dari data lama sehingga
     *   tidak wajib diisi ulang.
     * - 'user' punya aturan tambahan username/email unik.
     * - 'artikel' memakai validateArtikelFields().
     */
    protected function validateFromFields(Request $request, array $meta, bool $updating, ?Model $model): void
    {
        if (in_array($meta['slug'], ['artikel', 'artikel-pengendalian', 'artikel-sampah-lb3', 'artikel-tata-penataan', 'artikel-rth', 'data-tpu'], true)) {
            if (str_starts_with($meta['slug'], 'artikel')) {
                $this->validateArtikelFields($request, $updating, $model);
            }

            return;
        }

        $rules = [];
        $attributes = [];

        // Model pembanding skema: record saat update, atau instance baru
        // saat create (store() memanggil dengan $model = null).
        $schemaModel = $model ?? new $meta['model'];

        foreach (AdminRegistry::formFields($meta) as $field) {
            $name = $field['name'] ?? null;
            $type = $field['type'] ?? 'text';

            if (! $name || in_array($type, ['section', 'photos', 'relation_files', 'daftar_hadir'], true)) {
                continue;
            }
            if (($field['readonly'] ?? false) === true) {
                continue;
            }

            $required = ($field['required'] ?? false) === true;

            if (! array_key_exists('required', $field)) {
                $required = $this->columnIsRequired($schemaModel, $name);
            }

            if ($updating && ($field['readonly_on_edit'] ?? false) === true) {
                $required = false;
            }

            $rule = [];

            if ($type === 'password') {
                $rule[] = ($required && ! $updating) ? 'required' : 'nullable';
                $rule[] = 'string';
                $rule[] = Password::defaults();
            } elseif ($type === 'file') {
                $rule[] = ($required && ! $updating) ? 'required' : 'nullable';
                $rule[] = 'file';
                $rule[] = 'max:5120';
            } elseif ($type === 'checkbox') {
                $rule[] = 'nullable';
                $rule[] = 'boolean';
            } elseif ($type === 'number') {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'numeric';
                if (array_key_exists('min', $field)) {
                    $rule[] = 'min:'.$field['min'];
                }
                if (array_key_exists('max', $field)) {
                    $rule[] = 'max:'.$field['max'];
                }
                if (isset($field['decimal'])) {
                    $rule[] = 'decimal:'.$field['decimal'];
                }
            } elseif ($type === 'email') {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'email';
                $rule[] = 'max:255';
            } elseif ($type === 'date') {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'date';
            } elseif ($type === 'select' && Str::endsWith($name, '_id')) {
                $isManualSelection = filled($field['manual_field'] ?? null)
                    && $request->input($name) === '__lainnya__';

                $rule[] = $isManualSelection ? 'nullable' : ($required ? 'required' : 'nullable');
                if (! $isManualSelection) {
                    $rule[] = 'integer';
                }

                if ($isManualSelection) {
                    $manualField = (string) $field['manual_field'];
                    $rules[$manualField] = ['required', 'string', 'max:1000'];
                    $attributes[$manualField] = $field['manual_label'] ?? $field['label'].' (Manual)';
                }
            } else {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'string';
            }

            $rules[$name] = $rule;
            $attributes[$name] = $field['label'] ?? $name;
        }

        // Aturan khusus resource 'user': unique username/email.
        if ($meta['slug'] === 'user') {
            $ignoreId = $model?->getKey();
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique('user', 'username')->ignore($ignoreId)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique('user', 'email')->ignore($ignoreId)];
            $rules['role'] = ['nullable', Rule::in(collect(AdminRole::cases())->map(fn ($r) => $r->value)->all())];
            $attributes['username'] = 'Username';
            $attributes['email'] = 'Email';
            $attributes['role'] = 'Role';
        }

        // Satu catatan untuk satu tanggal dan satu periode mencegah data
        // rangkap pada grafik publik. Validasi juga menjaga nilai agar sesuai
        // dengan kapasitas kolom decimal(10,2) di basis data.
        if ($meta['slug'] === 'statistik-sampah') {
            $ignoreId = $model?->getKey();
            $rules['tanggal'] = [
                'required',
                'date',
                Rule::unique('statistik_sampah', 'tanggal')
                    ->where(fn ($query) => $query->where('periode', $request->input('periode')))
                    ->ignore($ignoreId),
            ];
            $rules['volume_ton'] = ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'];
            $rules['periode'] = ['required', Rule::in(array_keys(\App\Enums\StatistikSampahPeriode::options()))];
            $attributes['tanggal'] = 'Tanggal Pencatatan';
            $attributes['volume_ton'] = 'Volume Sampah';
            $attributes['periode'] = 'Periode Data';
        }

        if (! empty($rules)) {
            $request->validate($rules, [], $attributes);
        }
    }

    /**
     * Kolom dianggap wajib bila NOT NULL, tanpa default, dan bukan
     * auto increment. Hasilnya di-cache statis per kolom tabel.
     */
    protected function columnIsRequired(Model $model, string $column): bool
    {
        static $cache = [];

        $key = $model->getTable().'.'.$column;

        if (! array_key_exists($key, $cache)) {
            $definition = collect(Schema::getColumns($model->getTable()))
                ->firstWhere('name', $column);

            $cache[$key] = $definition !== null
                && ! ($definition['nullable'] ?? true)
                && ($definition['default'] ?? null) === null
                && ! ($definition['auto_increment'] ?? false);
        }

        return $cache[$key];
    }

    /**
     * Validasi khusus Artikel: semua field wajib diisi (judul, thumbnail, konten,
     * tanggal publish, status). Konten yang hanya berisi tag kosong (<p></p>, <br>,
     * &nbsp;) dianggap kosong. Thumbnail wajib diunggah saat create, atau saat update
     * bila belum ada file sebelumnya.
     */
    protected function validateArtikelFields(Request $request, bool $updating, ?Model $model): void
    {
        $articleType = $updating && $model instanceof Artikel
            ? ($model->isExternal() ? 'external' : 'internal')
            : (string) $request->input('article_type', 'internal');

        if ($articleType === 'external') {
            $request->validate([
                'article_type' => [$updating ? 'nullable' : 'required', Rule::in(['external'])],
                'external_url' => ['required', 'string', 'max:4096'],
                'tanggal_publish' => ['required', 'date'],
                'status' => ['required', Rule::in(array_keys(ArtikelStatus::options()))],
            ], [
                'article_type.required' => 'Mode Insert Link wajib dipilih.',
                'external_url.required' => 'Link berita wajib diisi.',
                'external_url.max' => 'Link berita terlalu panjang.',
                'tanggal_publish.required' => 'Tanggal tayang wajib diisi.',
                'tanggal_publish.date' => 'Tanggal tayang tidak valid.',
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid.',
            ]);

            return;
        }

        $thumbnailRule = ($updating && filled($model?->thumbnail))
            ? ['nullable', 'mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120']
            : ['required', 'mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'];

        $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'thumbnail' => $thumbnailRule,
            'komentar_enabled' => ['nullable', 'boolean'],
            'konten' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $cleaned = (string) $value;
                    do {
                        $prev = $cleaned;
                        $cleaned = preg_replace('/<(p|div|span)[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/\1>/i', '', $cleaned);
                    } while ($cleaned !== $prev);

                    $plain = trim(strip_tags($cleaned));
                    $hasMedia = (bool) preg_match('/<(img|video|table|iframe)\b/i', $cleaned);

                    if ($plain === '' && ! $hasMedia) {
                        $fail('Konten artikel wajib diisi.');
                    }
                },
            ],
            'tanggal_publish' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(ArtikelStatus::options()))],
        ], [
            'judul.required' => 'Judul artikel wajib diisi.',
            'thumbnail.required' => 'Gambar utama wajib diunggah.',
            'thumbnail.mimes' => 'Gambar utama harus berformat JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
            'thumbnail.max' => 'Ukuran gambar utama maksimal 5MB.',
            'konten.required' => 'Konten artikel wajib diisi.',
            'tanggal_publish.required' => 'Tanggal tayang wajib diisi.',
            'tanggal_publish.date' => 'Tanggal tayang tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ], [
            'judul' => 'Judul',
            'thumbnail' => 'Gambar Utama',
            'konten' => 'Konten',
            'tanggal_publish' => 'Tanggal Tayang',
            'status' => 'Status',
        ]);
    }

    protected function storeExternalArtikel(Request $request, array $meta)
    {
        try {
            $metadata = app(ExternalArticleMetadataService::class)
                ->preview(trim((string) $request->input('external_url')));
        } catch (ExternalArticleMetadataException $e) {
            throw ValidationException::withMessages(['external_url' => $e->getMessage()]);
        }

        /** @var Artikel $record */
        $record = new $meta['model'];
        $record->fill([
            'article_type' => 'external',
            'external_url' => trim((string) $request->input('external_url')),
            'external_thumbnail_url' => $metadata['image_url'],
            'judul' => $metadata['title'],
            'thumbnail' => null,
            'konten' => null,
            'tanggal_publish' => $request->input('tanggal_publish'),
            'status' => $request->input('status'),
            'komentar_enabled' => false,
        ]);
        $record->save();

        return redirect()->route('admin.resources.show', [$meta['slug'], $record])
            ->with('success', $meta['label'].' berhasil ditambahkan.');
    }

    protected function updateExternalArtikel(Request $request, array $meta, Artikel $model)
    {
        $externalUrl = trim((string) $request->input('external_url'));
        $urlChanged = $externalUrl !== trim((string) $model->external_url);

        if ($urlChanged) {
            try {
                $metadata = app(ExternalArticleMetadataService::class)->preview($externalUrl);
            } catch (ExternalArticleMetadataException $e) {
                throw ValidationException::withMessages(['external_url' => $e->getMessage()]);
            }

            $model->judul = $metadata['title'];
            $model->external_thumbnail_url = $metadata['image_url'];
            $model->external_url = $externalUrl;
        }

        $model->article_type = 'external';
        $model->konten = null;
        $model->komentar_enabled = false;
        $model->tanggal_publish = $request->input('tanggal_publish');
        $model->status = $request->input('status');
        $model->save();

        return redirect()->route('admin.resources.show', [$meta['slug'], $model])
            ->with('success', $meta['label'].' berhasil diperbarui.');
    }

    protected function storeSpecialRelations(Request $request, array $meta, Model $record): void
    {
        $compressionService = app(ImageCompressionService::class);

        foreach (AdminRegistry::relationUploads($meta['slug']) as $upload) {
            $name = $upload['name'];

            if (! $request->hasFile($name)) {
                continue;
            }

            foreach ((array) $request->file($name, []) as $file) {
                // 'image' => true memakai jalur kompresi WebP; file non-gambar
                // tetap lewat FileUploadService agar file sementara dibersihkan.
                $path = ($upload['image'] ?? false)
                    ? $compressionService->compressAndStore($file, $upload['directory'])
                    : app(FileUploadService::class)->store($file, $upload['directory'], 'public');

                if ($path === false) {
                    // Penyimpanan gagal (mis. gangguan koneksi ke B2) — lewati
                    // baris ini agar tidak menulis path kosong ke database.
                    continue;
                }

                $payload = array_merge($upload['defaults'] ?? [], [
                    $upload['foreign_key'] => $record->getKey(),
                    $upload['path_field'] => $path,
                ]);

                if (isset($upload['name_field'])) {
                    $payload[$upload['name_field']] = $file->getClientOriginalName();
                }

                $upload['model']::create($payload);
            }
        }
    }

    /**
     * Simpan daftar hadir (baris kunjungan) untuk kegiatan Monitoring & Evaluasi
     * pada resource 'sosialisasi' (menu Monitoring, Evaluasi dan Sosialisasi).
     * Baris yang masih ada diperbarui berdasarkan ID-nya agar perubahan tidak
     * berpindah ke baris lain dan data terkait peserta tetap terjaga.
     */
    protected function storeDaftarHadir(Request $request, array $meta, Model $record): void
    {
        if ($meta['slug'] !== 'sosialisasi') {
            return;
        }

        if ($request->input('jenis_kegiatan') !== 'monitoring-evaluasi') {
            return;
        }

        $rows = collect($request->input('daftar_hadir', []))
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => [
                'id' => filled($row['id'] ?? null) ? (int) $row['id'] : null,
                'nama_perusahaan' => trim((string) ($row['nama_perusahaan'] ?? '')),
                'jenis_usaha' => trim((string) ($row['jenis_usaha'] ?? '')),
                'tanggal' => ($row['tanggal'] ?? null) ?: null,
                'lokasi' => trim((string) ($row['lokasi'] ?? '')),
                'tim_survey' => trim((string) ($row['tim_survey'] ?? '')),
            ])
            // Pertahankan juga baris yang hanya memiliki jenis usaha atau tanggal;
            // sebelumnya kedua nilai ini justru terbuang saat form disimpan.
            ->filter(fn (array $row) => filled($row['nama_perusahaan'])
                || filled($row['jenis_usaha'])
                || filled($row['tanggal'])
                || filled($row['lokasi'])
                || filled($row['tim_survey']))
            ->values();

        $existingRows = $record->pesertas()
            ->get()
            ->keyBy(fn (Model $peserta) => (string) $peserta->getKey());

        $submittedIds = $rows
            ->pluck('id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (string) $id);

        if ($submittedIds->count() !== $submittedIds->unique()->count()) {
            throw ValidationException::withMessages([
                'daftar_hadir' => 'Setiap baris daftar hadir hanya boleh dikirim satu kali.',
            ]);
        }

        $invalidId = $submittedIds->first(fn (string $id) => ! $existingRows->has($id));
        if ($invalidId !== null) {
            throw ValidationException::withMessages([
                'daftar_hadir' => 'Salah satu baris daftar hadir tidak sesuai dengan kegiatan ini.',
            ]);
        }

        DB::transaction(function () use ($record, $existingRows, $rows): void {
            $savedIds = [];

            foreach ($rows as $row) {
                $id = $row['id'];
                unset($row['id']);

                if ($id !== null) {
                    /** @var Model $peserta */
                    $peserta = $existingRows->get((string) $id);
                    $peserta->update($row);
                    $savedIds[] = $peserta->getKey();

                    continue;
                }

                $savedIds[] = $record->pesertas()->create($row)->getKey();
            }

            $recordsToDelete = $record->pesertas();
            if ($savedIds === []) {
                $recordsToDelete->delete();

                return;
            }

            $recordsToDelete->whereNotIn('id', $savedIds)->delete();
        });
    }

    protected function storeSanksiIfPelanggaran(Request $request, Model $record): void
    {
        if (! ($record instanceof Pelanggaran)) {
            return;
        }

        $sanksiData = array_filter([
            'jenis_sanksi' => $request->input('jenis_sanksi'),
            'status_sanksi' => $request->input('status_sanksi'),
            'batas_waktu_perbaikan' => $request->input('batas_waktu_perbaikan'),
            'catatan' => $request->input('catatan_sanksi'),
        ], fn ($v) => $v !== null && $v !== '');

        if (empty($sanksiData)) {
            return;
        }

        $sanksi = $record->sanksi;
        if ($sanksi) {
            $sanksi->update($sanksiData);
        } else {
            Sanksi::create(array_merge($sanksiData, [
                'pelanggaran_id' => $record->id,
            ]));
        }
    }
}
