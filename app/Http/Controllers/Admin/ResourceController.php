<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Bidang;
use App\Http\Controllers\Controller;
use App\Models\LaporanFoto;
use App\Services\ImageCompressionService;
use App\Support\Admin\AdminRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        
        // Cek apakah user bisa akses group dari resource ini
        // (atau slug menu spesifik yang diberikan sebagai akses tambahan).
        if (!$user->canAccessResource($meta)) {
            throw new AccessDeniedHttpException('Anda tidak memiliki izin untuk mengakses menu ini. Silakan hubungi administrator.');
        }
    }

    public function index(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $this->query($meta, $request);

        $view = match($meta['slug']) {
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

        $view = match($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
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
        
        // Handle role assignment untuk user
        if ($resource === 'user' && $request->filled('role')) {
            $record->syncRoles([$request->input('role')]);
        }
        
        // Handle additional_access untuk user
        if ($resource === 'user' && $request->has('additional_access')) {
            $record->additional_access = $request->input('additional_access', []);
            $record->save();
        }

        // Simpan foto profil user (bila diunggah).
        if ($resource === 'user' && $request->hasFile('photo')) {
            $record->photo_path = $request->file('photo')->store('avatars', 'public');
            $record->save();
        }

        return redirect()->route('admin.resources.show', [$resource, $record])->with('success', $meta['label'].' berhasil ditambahkan.');
    }

    public function show(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $model = $meta['model']::findOrFail($record);

        $view = match($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.show',
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

        $view = match($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
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
        
        // Handle role assignment untuk user
        if ($resource === 'user' && $request->filled('role')) {
            // Jangan update role jika user adalah superadmin (protection)
            if (!$model->isSuperadmin()) {
                $model->syncRoles([$request->input('role')]);
            }
        }
        
        // Handle additional_access untuk user
        if ($resource === 'user' && $request->has('additional_access')) {
            $model->additional_access = $request->input('additional_access', []);
            $model->save();
        }

        // Foto profil: hapus bila diminta, atau ganti bila ada file baru.
        if ($resource === 'user') {
            if ($request->boolean('photo_remove') && $model->photo_path) {
                if (Storage::disk('public')->exists($model->photo_path)) {
                    Storage::disk('public')->delete($model->photo_path);
                }
                $model->photo_path = null;
                $model->save();
            } elseif ($request->hasFile('photo')) {
                if ($model->photo_path && Storage::disk('public')->exists($model->photo_path)) {
                    Storage::disk('public')->delete($model->photo_path);
                }
                $model->photo_path = $request->file('photo')->store('avatars', 'public');
                $model->save();
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

        $target = \App\Models\User::findOrFail($record);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $target->password = Hash::make($validated['password']);
        $target->password_hint = $validated['password'];
        $target->save();

        return redirect()
            ->route('admin.resources.show', ['user', $target])
            ->with('success', 'Password untuk ' . $target->name . ' berhasil direset. Password baru: ' . $validated['password']);
    }

    /**
     * Unduh dokumen/lampiran (field file maupun relasi dokumen) dari storage publik.
     * Path divalidasi agar tetap berada di dalam direktori storage/app/public
     * sehingga tidak bisa digunakan untuk mengakses file di luar storage publik.
     */
    public function downloadFile(Request $request)
    {
        $path = (string) $request->query('path', '');
        $name = (string) $request->query('name', '');

        abort_unless($path !== '' && ! str_contains($path, '..'), 403, 'Akses file ditolak.');
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
                $downloadName = $base . '.' . $pathExt;
            } elseif ($nameExt !== $pathExt) {
                // Ekstensi tidak cocok dengan file asli -> ganti agar sesuai format.
                $downloadName = pathinfo($downloadName, PATHINFO_FILENAME) . '.' . $pathExt;
            }
        }

        return Storage::disk('public')->download($path, $downloadName);
    }

    public function destroy(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $model = $meta['model']::findOrFail($record);
        $model->delete();

        return redirect()->route('admin.resources.index', $resource)->with('success', $meta['label'].' berhasil dihapus.');
    }

    public function export(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $this->query($meta, $request);

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'filter');
    }

    public function exportAll(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $meta['model']::query()->orderByDesc((new $meta['model'])->getKeyName());

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'all');
    }

    public function bulkExport(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $ids = $request->input('ids', []);
        $query = $meta['model']::query()->whereIn('id', $ids);

        return $this->downloadData($meta, $query, $request->string('format', 'xlsx')->toString(), 'bulk');
    }

    /**
     * Unduh data resource dalam format xlsx | csv.
     */
    protected function downloadData(array $meta, $query, string $format, string $scope)
    {
        $format = in_array($format, ['xlsx', 'csv'], true) ? $format : 'xlsx';
        // Ekspor selalu berisi DATA LENGKAP tiap menu (semua kolom tabel),
        // bukan sekadar subset $meta['columns'].
        $exportMap = $meta['exportColumns']
            ?? array_combine($meta['columns'], $meta['columns']);
        $columns = array_keys($exportMap);
        $headings = array_values($exportMap);
        $filename = $meta['slug'].'-'.$scope.'-'.now()->format('Ymd-His');

        \App\Support\ActivityLogger::log('exported', $meta['label'].' ('.strtoupper($format).', '.$scope.')', $meta['slug']);

        $dataIO = app(\App\Support\DataIO::class);

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
                $record->delete();
                $deleted++;
            }
        }

        return redirect()->route('admin.resources.index', $resource)
            ->with('success', $deleted.' '.$meta['label'].' berhasil dihapus.');
    }

    protected function query(array $meta, Request $request)
    {
        $model = new $meta['model'];
        $query = $meta['model']::query();
        
        // Custom scope untuk resource pengaduan berdasarkan bidang
        if ($meta['slug'] === 'pengaduan-pengendalian') {
            $query->where('bidang', 'pengendalian');
        } elseif ($meta['slug'] === 'pengaduan-sampah') {
            $query->where('bidang', 'sampah-lb3');
        } elseif ($meta['slug'] === 'pengaduan-rth') {
            $query->where('bidang', 'rth');
        } elseif ($meta['slug'] === 'laporan') {
            // Resource 'laporan' menampilkan semua kategori sesuai allowed groups
            // (sudah dihandle di authorization)
        }
        
        // Filtering generik berdasarkan definisi $meta['filters'].
        foreach (($meta['filters'] ?? []) as $filter) {
            $key    = $filter['key'] ?? null;
            $type   = $filter['type'] ?? null;
            $column = $filter['column'] ?? null;

            if (! $key || ! $type || ! $column) {
                continue;
            }

            if ($type === 'daterange') {
                // Terima {key}_from/{key}_to, kompatibel dengan date_from/date_to lama.
                $from = $request->input($key.'_from') ?? $request->input('date_from');
                $to   = $request->input($key.'_to') ?? $request->input('date_to');

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

        if ($sortColumn && in_array($sortColumn, $meta['columns']) && !in_array($sortColumn, $virtualColumns)) {
            $query->orderBy($sortColumn, in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc');
        } else {
            $query->orderByDesc($model->getKeyName());
        }

        // Eager load relationships for specific resources
        if ($meta['slug'] === 'pelanggaran') {
            $query->with('sanksi');
        }
        
        // Search
        $search = trim($request->string('q')->toString());

        if ($search !== '') {
            $columns = collect(array_merge($meta['columns'], $model->getFillable()))
                ->unique()
                ->reject(fn ($column) => Str::endsWith($column, '_id') || in_array($column, ['password', 'remember_token', 'email_verified_at', 'additional_access', 'photo_path', 'preferences', 'role', 'password_hint']))
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
                        $payload[$name] = $file->store('admin/'.$meta['slug'], 'public');
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
                    $plain = $request->input($name);
                    $payload[$name] = Hash::make($plain);

                    // Petunjuk password otomatis mengikuti password yang diisi.
                    // Field password_hint disembunyikan saat create & read-only saat edit,
                    // sehingga nilainya diambil dari password agar tidak pernah NULL
                    // (sebelumnya akun baru / edit tanpa petunjuk jadi "(belum ada petunjuk)").
                    $payload['password_hint'] = $plain;
                }

                continue;
            }

            if ($name === 'password_hint') {
                // Sudah diisi otomatis dari password di atas bila password diubah.
                // Fallback: pertahankan nilai lama saat edit tanpa mengganti password.
                if (! array_key_exists('password_hint', $payload) && $request->filled($name)) {
                    $payload[$name] = $request->input($name);
                }

                continue;
            }

            if ($request->exists($name)) {
                $payload[$name] = $request->input($name) === '' ? null : $request->input($name);
            }
        }

        if ($meta['slug'] === 'pengaduan-pengendalian') {
            $payload['bidang'] = Bidang::PENGENDALIAN->value;

            if (array_key_exists('jenis_pengaduan', $payload)) {
                $payload['kategori'] = $payload['jenis_pengaduan'];
            }
        } elseif ($meta['slug'] === 'pengaduan-sampah') {
            $payload['bidang'] = Bidang::SAMPAH_LB3->value;

            if (array_key_exists('jenis_pengaduan', $payload)) {
                $payload['kategori'] = $payload['jenis_pengaduan'];
            }
        } elseif ($meta['slug'] === 'pengaduan-rth') {
            $payload['bidang'] = Bidang::RTH->value;

            if (array_key_exists('jenis_pengaduan', $payload)) {
                $payload['kategori'] = $payload['jenis_pengaduan'];
            }
        } elseif ($meta['slug'] === 'pinjam-taman') {
            // Taman "Lainnya" -> simpan nama manual dan kosongkan relasi taman_kota_id.
            if (($payload['taman_kota_id'] ?? null) === '__lainnya__' && $request->filled('taman_kota_id_lainnya')) {
                $payload['nama_taman_manual'] = $request->input('taman_kota_id_lainnya');
                $payload['taman_kota_id'] = null;
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
    protected function fileMatchesAccept(\Illuminate\Http\UploadedFile $file, ?string $accept): bool
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
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
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
                if (str_starts_with($token, 'image/') && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true)) {
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
                'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'photo_remove' => ['nullable', 'boolean'],
            ], [
                'photo.image' => 'File foto profil harus berupa gambar.',
                'photo.mimes' => 'Foto profil harus berformat JPG, PNG, atau WEBP.',
                'photo.max' => 'Ukuran foto profil maksimal 2MB.',
            ]);

            return;
        }

        $pengaduanSlugs = ['pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth'];

        if (! in_array($meta['slug'], $pengaduanSlugs)) {
            return;
        }

        $jenisOptions = match ($meta['slug']) {
            'pengaduan-pengendalian' => \App\Enums\JenisPengaduanPengendalian::options(),
            'pengaduan-sampah' => \App\Enums\JenisPengaduanSampah::options(),
            'pengaduan-rth' => \App\Enums\JenisPengaduanRth::options(),
        };

        $statusOptions = match ($meta['slug']) {
            'pengaduan-rth' => \App\Enums\StatusPengaduanRth::options(),
            default => \App\Enums\PengaduanStatus::options(),
        };

        $status = $request->input('status');
        $isDitolak = $meta['slug'] === 'pengaduan-rth'
            ? $status === \App\Enums\StatusPengaduanRth::DITOLAK->value
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
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'photos.*.image' => 'File foto bukti harus berupa gambar.',
            'photos.*.mimes' => 'Foto bukti harus berformat JPG atau PNG.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 2MB.',
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
            $request->validate([
                'judul' => ['required', 'string', 'max:255'],
                'konten' => ['required', 'string'],
            ], [], [
                'judul' => 'Judul',
                'konten' => 'Konten',
            ]);

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
                $rule[] = 'min:6';
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
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($ignoreId)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)];
            $rules['role'] = ['nullable', Rule::in(collect(\App\Enums\AdminRole::cases())->map(fn ($r) => $r->value)->all())];
            $attributes['username'] = 'Username';
            $attributes['email'] = 'Email';
            $attributes['role'] = 'Role';
        }

        if (! empty($rules)) {
            $request->validate($rules, [], $attributes);
        }
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
                $path = ($upload['image'] ?? false)
                    ? $compressionService->compressAndStore($file, $upload['directory'])
                    : $file->store($upload['directory'], 'public');

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
    {        if (! ($record instanceof \App\Models\Pelanggaran)) {
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
            \App\Models\Sanksi::create(array_merge($sanksiData, [
                'pelanggaran_id' => $record->id,
            ]));
        }
    }
}
