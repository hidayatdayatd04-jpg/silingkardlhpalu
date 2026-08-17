<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Enums\ArtikelStatus;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanRth;
use App\Enums\JenisPengaduanSampah;
use App\Enums\PengaduanStatus;
use App\Enums\StatusPengaduanRth;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExportJob;
use App\Models\Pelanggaran;
use App\Models\Sanksi;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\ImageCompressionService;
use App\Support\ActivityLogger;
use App\Support\Admin\AdminRegistry;
use App\Support\DataIO;
use App\Support\HtmlSanitizer;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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

    public function index(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $this->query($meta, $request);

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

        abort_if(($meta['can_create'] ?? true) === false, 403,
            'Menu ini hanya mendukung lihat detail dan edit. Penambahan data tidak diizinkan.');

        $view = match ($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
            'artikel' => 'admin.artikel.form',
            default => 'admin.resources.form',
        };

        return view($view, [
            'resource' => $meta,
            'record' => new $meta['model'],
            'fields' => AdminRegistry::formFields($meta),
            'method' => 'POST',
            'action' => route('admin.resources.store', $resource),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);

        abort_if(($meta['can_create'] ?? true) === false, 403,
            'Menu ini hanya mendukung lihat detail dan edit. Penambahan data tidak diizinkan.');
        $this->validateSpecialFields($request, $meta, false);
        $this->validateFromFields($request, $meta, false, null);

        $record = new $meta['model'];
        $record->fill($this->payload($request, $meta, $record));
        $record->save();
        $this->storeSpecialRelations($request, $meta, $record);
        $this->storeDaftarHadir($request, $meta, $record);
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
        $model = $meta['model']::findOrFail($record);

        $view = match ($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
            'artikel' => 'admin.artikel.form',
            default => 'admin.resources.form',
        };

        return view($view, [
            'resource' => $meta,
            'record' => $model,
            'fields' => AdminRegistry::formFields($meta),
            'method' => 'PUT',
            'action' => route('admin.resources.update', [$resource, $model]),
        ]);
    }

    public function update(Request $request, string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->validateSpecialFields($request, $meta, true);

        $model = $meta['model']::findOrFail($record);
        $this->validateFromFields($request, $meta, true, $model);
        $model->fill($this->payload($request, $meta, $model));
        $model->save();
        $this->storeSpecialRelations($request, $meta, $model);
        $this->storeDaftarHadir($request, $meta, $model);
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

        // Batasi path ke direktori penyimpanan milik resource ini saja:
        // 'admin/{slug}/' (field file langsung) atau direktori upload relasi
        // (foto pengaduan, dokumen permohonan, file sosialisasi, dll).
        $allowedPrefixes = ['admin/'.$meta['slug'].'/'];
        foreach (AdminRegistry::relationUploads($meta['slug']) as $upload) {
            $allowedPrefixes[] = $upload['directory'].'/';
        }
        abort_unless(
            collect($allowedPrefixes)->contains(fn (string $prefix) => str_starts_with($path, $prefix)),
            403,
            'Akses file ditolak.'
        );

        abort_unless(Storage::disk('public')->exists($path), 404, 'File tidak ditemukan.');

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

        return Storage::disk('public')->download($path, $downloadName);
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
        // Ekspor selalu berisi DATA LENGKAP tiap menu (semua kolom tabel),
        // bukan sekadar subset $meta['columns'].
        $exportMap = AdminRegistry::exportColumns($meta['slug'], $meta['model'])
            ?? array_combine($meta['columns'], $meta['columns']);
        $columns = array_keys($exportMap);
        $headings = array_values($exportMap);
        $filename = $meta['slug'].'-'.$scope.'-'.now()->format('Ymd-His');

        ActivityLogger::log('exported', $meta['label'].' ('.strtoupper($format).', '.$scope.')', $meta['slug']);

        $dataIO = app(DataIO::class);

        if ($format === 'csv') {
            return $dataIO->csvDownload($query, $columns, $filename.'.csv', $headings);
        }

        // XLSX via DataIO (ZipArchive-based, dependency-free).
        $tmpPath = storage_path('app/private/'.$filename.'.xlsx');
        $dataIO->writeXlsx($query, $columns, $tmpPath, $headings);

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
            // Kolom penulis di index artikel memakai relasi user.
            $query->with('user');
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

        foreach (AdminRegistry::formFields($meta) as $field) {
            $name = $field['name'];

            if (in_array($field['type'], ['section', 'photos', 'relation_files', 'daftar_hadir'], true)) {
                continue;
            }

            if ($field['type'] === 'file') {
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

            if ($field['type'] === 'checkbox') {
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

            if ($request->exists($name)) {
                $payload[$name] = $request->input($name) === '' ? null : $request->input($name);
            }
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

        $pengaduanSlugs = ['pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth'];

        if (! in_array($meta['slug'], $pengaduanSlugs)) {
            return;
        }

        $jenisOptions = match ($meta['slug']) {
            'pengaduan-pengendalian' => JenisPengaduanPengendalian::options(),
            'pengaduan-sampah' => JenisPengaduanSampah::options(),
            'pengaduan-rth' => JenisPengaduanRth::options(),
        };

        $statusOptions = match ($meta['slug']) {
            'pengaduan-rth' => StatusPengaduanRth::options(),
            default => PengaduanStatus::options(),
        };

        $status = $request->input('status');
        $isDitolak = $meta['slug'] === 'pengaduan-rth'
            ? $status === StatusPengaduanRth::DITOLAK->value
            : false;

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
            'alasan_penolakan' => $meta['slug'] === 'pengaduan-rth' ? [$isDitolak ? 'required' : 'nullable', 'string'] : ['nullable', 'string'],
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
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi saat status Ditolak.',
            'photos.required' => 'Foto bukti wajib diunggah.',
            'photos.max' => 'Maksimal 5 foto bukti.',
            'photos.*.mimes' => 'Foto bukti harus berformat JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 5MB.',
        ]);
    }

    /**
     * Validasi server tambahan.
     * - 'user': mencegah error NOT NULL (500) pada name/username/password saat
     *   create, dan menjamin username/email unik.
     * - 'artikel': memastikan judul wajib diisi (mencegah slug/model crash).
     * Resource lain memakai atribut required pada form serta
     * validateSpecialFields() untuk pengaduan.
     */
    protected function validateFromFields(Request $request, array $meta, bool $updating, ?Model $model): void
    {
        if (in_array($meta['slug'], ['artikel', 'artikel-pengendalian', 'artikel-sampah-lb3', 'artikel-tata-penataan', 'artikel-rth'], true)) {
            $this->validateArtikelFields($request, $updating, $model);

            return;
        }

        if ($meta['slug'] !== 'user') {
            return;
        }

        $rules = [];
        $attributes = [];

        foreach (AdminRegistry::formFields($meta) as $field) {
            $name = $field['name'] ?? null;
            $type = $field['type'] ?? 'text';

            if (! $name || in_array($type, ['section', 'photos', 'relation_files'], true)) {
                continue;
            }
            if (($field['readonly'] ?? false) === true) {
                continue;
            }

            $required = ($field['required'] ?? false) === true;
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
            } elseif ($type === 'email') {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'email';
                $rule[] = 'max:255';
            } elseif ($type === 'date') {
                $rule[] = $required ? 'required' : 'nullable';
                $rule[] = 'date';
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

        if (! empty($rules)) {
            $request->validate($rules, [], $attributes);
        }
    }

    /**
     * Validasi khusus Artikel: semua field wajib diisi (judul, thumbnail, konten,
     * tanggal publish, status). Konten yang hanya berisi tag kosong (<p></p>, <br>,
     * &nbsp;) dianggap kosong. Thumbnail wajib diunggah saat create, atau saat update
     * bila belum ada file sebelumnya.
     */
    protected function validateArtikelFields(Request $request, bool $updating, ?Model $model): void
    {
        $thumbnailRule = ($updating && filled($model?->thumbnail))
            ? ['nullable', 'mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120']
            : ['required', 'mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'];

        $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'thumbnail' => $thumbnailRule,
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
            'thumbnail.required' => 'Thumbnail wajib diunggah.',
            'thumbnail.mimes' => 'Thumbnail harus berformat JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 5MB.',
            'konten.required' => 'Konten artikel wajib diisi.',
            'tanggal_publish.required' => 'Tanggal publish wajib diisi.',
            'tanggal_publish.date' => 'Tanggal publish tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ], [
            'judul' => 'Judul',
            'thumbnail' => 'Thumbnail',
            'konten' => 'Konten',
            'tanggal_publish' => 'Tanggal Publish',
            'status' => 'Status',
        ]);
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
     * Baris lama dihapus lalu dibuat ulang agar selalu sinkron dengan form.
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
            ->filter(fn ($row) => is_array($row) && (filled($row['nama_perusahaan'] ?? null) || filled($row['lokasi'] ?? null) || filled($row['tim_survey'] ?? null)))
            ->map(fn ($row) => [
                'nama_perusahaan' => trim((string) ($row['nama_perusahaan'] ?? '')),
                'jenis_usaha' => trim((string) ($row['jenis_usaha'] ?? '')),
                'tanggal' => ($row['tanggal'] ?? null) ?: null,
                'lokasi' => trim((string) ($row['lokasi'] ?? '')),
                'tim_survey' => trim((string) ($row['tim_survey'] ?? '')),
            ])
            ->values();

        $record->pesertas()->delete();

        foreach ($rows as $row) {
            $record->pesertas()->create($row);
        }
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
