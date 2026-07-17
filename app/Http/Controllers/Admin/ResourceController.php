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
        if (!$user->canAccessGroup($meta['group'])) {
            throw new AccessDeniedHttpException('Anda tidak memiliki izin untuk mengakses menu ini. Silakan hubungi administrator.');
        }
    }

    public function index(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $query = $this->query($meta, $request);

        $view = match($meta['slug']) {
            'ikm-response' => 'admin.ikm.index',
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

        $view = match($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.form',
            'ikm-response' => 'admin.ikm.form',
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
        $this->validateSpecialFields($request, $meta, false);

        $record = new $meta['model'];
        $record->fill($this->payload($request, $meta, $record));
        $record->save();
        $this->storeSpecialRelations($request, $meta, $record);
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

        return redirect()->route('admin.resources.show', [$resource, $record])->with('success', $meta['label'].' berhasil ditambahkan.');
    }

    public function show(string $resource, int|string $record)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $model = $meta['model']::findOrFail($record);

        $view = match($meta['slug']) {
            'pengaduan-pengendalian', 'pengaduan-sampah', 'pengaduan-rth' => 'admin.pengendalian.show',
            'ikm-response' => 'admin.ikm.show',
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
            'ikm-response' => 'admin.ikm.form',
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
        $model->fill($this->payload($request, $meta, $model));
        $model->save();
        $this->storeSpecialRelations($request, $meta, $model);
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

        return redirect()->route('admin.resources.show', [$resource, $model])->with('success', $meta['label'].' berhasil diperbarui.');
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
     * Unduh data resource dalam format xlsx | csv | pdf.
     */
    protected function downloadData(array $meta, $query, string $format, string $scope)
    {
        $format = in_array($format, ['xlsx', 'csv', 'pdf'], true) ? $format : 'xlsx';
        $columns = $meta['columns'];
        $filename = $meta['slug'].'-'.$scope.'-'.now()->format('Ymd-His');

        \App\Support\ActivityLogger::log('exported', $meta['label'].' ('.strtoupper($format).', '.$scope.')', $meta['slug']);

        $dataIO = app(\App\Support\DataIO::class);

        if ($format === 'pdf') {
            $rows = (clone $query)->limit(5000)->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.resource-table', [
                'title'   => $meta['label'],
                'columns' => $columns,
                'rows'    => $rows,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($filename.'.pdf');
        }

        if ($format === 'csv') {
            return $dataIO->csvDownload($query, $columns, $filename.'.csv');
        }

        // XLSX via DataIO (ZipArchive-based, dependency-free).
        $tmpPath = storage_path('app/private/'.$filename.'.xlsx');
        $dataIO->writeXlsx($query, $columns, $tmpPath);

        return response()->download($tmpPath, $filename.'.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Import data dari file .xlsx / .csv.
     */
    public function import(Request $request, string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);
        $this->authorize('create', $meta['model']);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'file.required' => 'Silakan pilih file untuk diimpor.',
            'file.mimes'    => 'Format file harus .xlsx atau .csv.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $import = new \App\Imports\ResourceImport($meta);

        try {
            $rows = \App\Support\DataIO::readFile($request->file('file')->getRealPath());
            $import->collection($rows);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file: '.$e->getMessage());
        }

        \App\Support\ActivityLogger::log('imported', $meta['label'].' ('.$import->imported.' baris)', $meta['slug']);

        if (! empty($import->errors)) {
            $preview = collect($import->errors)->take(5)->implode(' | ');

            return back()->with('warning', "Berhasil impor {$import->imported} baris. ".count($import->errors)." baris gagal: ".$preview);
        }

        return back()->with('success', "Berhasil mengimpor {$import->imported} baris data.");
    }

    /**
     * Unduh template import (hanya header kolom).
     */
    public function importTemplate(string $resource)
    {
        $meta = AdminRegistry::find($resource);
        $this->authorize($meta);

        $model = new $meta['model'];
        $readonly = ['id', 'nomor_tiket', 'nomor_pengajuan', 'nomor_registrasi', 'nomor_sidak', 'nomor_pelanggaran', 'nomor_sanksi', 'created_at', 'updated_at', 'password', 'remember_token', 'email_verified_at', 'additional_access', 'photo_path', 'preferences'];
        $columns = collect($model->getFillable())
            ->reject(fn ($c) => in_array($c, $readonly, true))
            ->values()
            ->all();

        $emptyQuery = $meta['model']::query()->whereRaw('1 = 0');
        $filename = $meta['slug'].'-template.xlsx';
        $tmpPath = storage_path('app/private/'.$filename);

        app(\App\Support\DataIO::class)->writeXlsx($emptyQuery, $columns, $tmpPath);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
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
        
        // Filtering
        // Status filter (multi-select)
        if ($request->has('status') && is_array($request->status)) {
            $statuses = array_filter($request->status);
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Sorting
        $sortColumn = $request->string('sort')->toString();
        $sortDirection = $request->string('direction', 'asc')->toString();
        
        if ($sortColumn && in_array($sortColumn, $meta['columns'])) {
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
                ->reject(fn ($column) => Str::endsWith($column, '_id') || in_array($column, ['password', 'remember_token', 'email_verified_at', 'additional_access', 'photo_path', 'preferences']))
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

            if (in_array($field['type'], ['section', 'photos', 'relation_files'], true)) {
                continue;
            }

            if ($field['type'] === 'file') {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    $allowedMimes = $field['accept'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx';
                    $mimes = collect(explode(',', $allowedMimes))->map(fn ($m) => trim($m))->filter()->values()->all();
                    if (! empty($mimes) && ! in_array($file->getClientOriginalExtension(), $mimes, true)) {
                        continue;
                    }
                    $payload[$name] = $file->store('admin/'.$meta['slug'], 'public');
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
        }

        // Handle 'lainnya' (other) option for SIDAK fields
        if ($meta['slug'] === 'sidak') {
            // Objek Pengawasan Lainnya
            if (($payload['objek_pengawasan_id'] ?? null) === '__lainnya__' && $request->filled('objek_pengawasan_lainnya')) {
                $objek = \App\Models\ObjekPengawasan::create([
                    'nama_perusahaan' => $request->input('objek_pengawasan_lainnya'),
                ]);
                $payload['objek_pengawasan_id'] = $objek->id;
            }

            // Pengaduan Tata Penataan Lainnya
            if (($payload['pengaduan_tata_penataan_id'] ?? null) === '__lainnya__' && $request->filled('pengaduan_tata_penataan_lainnya')) {
                $pengaduan = \App\Models\PengaduanTataPenataan::create([
                    'nama_pelapor' => $request->input('pengaduan_tata_penataan_lainnya'),
                    'deskripsi' => 'Dibuat dari form sidak (lainnya)',
                ]);
                $payload['pengaduan_tata_penataan_id'] = $pengaduan->id;
            }

            // User/Petugas Lainnya
            if (($payload['user_id'] ?? null) === '__lainnya__' && $request->filled('user_lainnya')) {
                $user = \App\Models\User::create([
                    'name' => $request->input('user_lainnya'),
                    'email' => strtolower(str_replace(' ', '.', $request->input('user_lainnya'))) . '@dlh-palu.go.id',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]);
                $payload['user_id'] = $user->id;
            }

            // Hasil Lainnya - store as custom string value
            if (($payload['hasil'] ?? null) === '__lainnya__' && $request->filled('hasil_lainnya')) {
                $payload['hasil'] = $request->input('hasil_lainnya');
            }
        }

        return $payload;
    }

    protected function validateSpecialFields(Request $request, array $meta, bool $updating): void
    {
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
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'max:30'],
            'jenis_pengaduan' => ['required', Rule::in(array_keys($jenisOptions))],
            'alamat' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(array_keys($statusOptions))],
            'catatan_admin' => ['nullable', 'string'],
            'alasan_penolakan' => [$isDitolak ? 'required' : 'nullable', 'string'],
            'bukti_foto_selesai' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
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

    protected function storeSanksiIfPelanggaran(Request $request, Model $record): void
    {
        if (! ($record instanceof \App\Models\Pelanggaran)) {
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
