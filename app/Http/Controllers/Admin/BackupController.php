<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BackupController extends Controller
{
    protected function authorizeSuperadmin(): void
    {
        if (! auth()->user()?->isSuperadmin()) {
            throw new AccessDeniedHttpException('Hanya Admin yang dapat mengakses backup database.');
        }
    }

    public function index()
    {
        $this->authorizeSuperadmin();

        return view('admin.backup.index', [
            'backups'  => DatabaseBackup::listBackups(),
            'database' => config('database.connections.'.config('database.default').'.database'),
        ]);
    }

    public function store()
    {
        $this->authorizeSuperadmin();
        set_time_limit(600);

        try {
            $path = app(DatabaseBackup::class)->dump();
            $name = basename($path);

            ActivityLogger::log('backup', 'Backup database: '.$name, 'system');

            return back()->with('success', 'Backup berhasil dibuat: '.$name);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat backup: '.$e->getMessage());
        }
    }

    public function download(string $file)
    {
        $this->authorizeSuperadmin();

        $path = DatabaseBackup::safePath($file);
        abort_if($path === null, 404, 'File backup tidak ditemukan.');

        $name = basename($path);
        $headers = [];
        if (str_ends_with($name, '.zip')) {
            $headers['Content-Type'] = 'application/zip';
        }

        return response()->download($path, $name, $headers);
    }

    public function restore(Request $request)
    {
        $this->authorizeSuperadmin();

        // Backup/restore butuh waktu — naikkan timeout.
        set_time_limit(600);

        $request->validate([
            'file'         => ['nullable', 'file', 'max:102400'],
            'existing'     => ['nullable', 'string'],
            'confirmation' => ['required', 'in:RESTORE'],
        ], [
            'confirmation.required' => 'Ketik RESTORE untuk konfirmasi.',
            'confirmation.in'       => 'Konfirmasi tidak valid. Ketik RESTORE persis.',
            'file.max'              => 'Ukuran file terlalu besar (maks 100MB).',
        ]);

        try {
            $service = app(DatabaseBackup::class);

            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');

                if (! $uploadedFile->isValid()) {
                    return back()->with('error', 'File gagal diunggah. Silakan coba lagi.');
                }

                $originalName = $uploadedFile->getClientOriginalName();
                $extension = strtolower($uploadedFile->getClientOriginalExtension());

                if (! in_array($extension, ['zip', 'sql', 'txt'])) {
                    return back()->with('error', 'Format file tidak valid. Hanya file .zip atau .sql yang diterima.');
                }

                // Amankan backup saat ini dulu sebelum overwrite.
                $service->dump('pre-restore-'.now()->format('Ymd-His').'.zip');

                $fullPath = $uploadedFile->getRealPath();

                if (! is_file($fullPath)) {
                    return back()->with('error', 'File temporary tidak ditemukan. Silakan unggah ulang.');
                }

                $count = $service->restore($fullPath);
                $source = $originalName;
            } elseif ($request->filled('existing')) {
                $fullPath = DatabaseBackup::safePath($request->input('existing'));
                abort_if($fullPath === null, 404, 'File backup tidak ditemukan.');
                $service->dump('pre-restore-'.now()->format('Ymd-His').'.zip');
                $count = $service->restore($fullPath);
                $source = basename($request->input('existing'));
            } else {
                return back()->with('error', 'Pilih file backup atau unggah file .sql untuk restore.');
            }

            ActivityLogger::log('restore', 'Restore database dari: '.$source.' ('.$count.' statement)', 'system');

            return back()->with('success', "Restore berhasil dari {$source}. {$count} statement dieksekusi.");
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal restore: '.$e->getMessage());
        }
    }

    public function destroy(string $file)
    {
        $this->authorizeSuperadmin();

        $path = DatabaseBackup::safePath($file);
        abort_if($path === null, 404, 'File backup tidak ditemukan.');

        Storage::disk(DatabaseBackup::DISK)->delete(DatabaseBackup::DIR.'/'.basename($file));

        ActivityLogger::log('deleted', 'Hapus backup: '.basename($file), 'system');

        return back()->with('success', 'Backup '.basename($file).' berhasil dihapus.');
    }
}
