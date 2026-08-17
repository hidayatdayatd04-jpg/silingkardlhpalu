<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupJob;
use App\Jobs\RunRestoreJob;
use App\Services\FileUploadService;
use App\Support\ActivityLogger;
use App\Support\BackupProgress;
use App\Support\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BackupController extends Controller
{
    /** Folder persisten untuk file restore hasil upload (diproses job queue). */
    protected const RESTORE_UPLOAD_DIR = 'app/private/backup-restore';

    protected function authorizeSuperadmin(): void
    {
        if (! auth()->user()?->isSuperadmin()) {
            throw new AccessDeniedHttpException('Hanya Admin yang dapat mengakses backup database.');
        }
    }

    public function index()
    {
        $this->authorizeSuperadmin();

        // Konfirmasi dua langkah: kode acak per-sesi yang harus diketik ulang
        // saat restore, selain kata RESTORE. Kode di-regenerasi tiap kali
        // halaman dibuka dan hangus setelah satu kali restore sukses.
        $restoreCode = strtoupper(Str::random(6));
        session(['restore_code' => $restoreCode]);

        return view('admin.backup.index', [
            'backups' => DatabaseBackup::listBackups(),
            'database' => config('database.connections.'.config('database.default').'.database'),
            'restoreCode' => $restoreCode,
        ]);
    }

    public function store()
    {
        $this->authorizeSuperadmin();

        if (! BackupProgress::start('backup')) {
            return back()->with('error', 'Masih ada proses backup/restore yang berjalan. Tunggu hingga selesai atau batalkan terlebih dahulu.');
        }

        RunBackupJob::dispatch(auth()->id());

        return back()->with('info', 'Backup sedang berjalan di latar belakang. Anda bebas berpindah halaman — progres tampil di pojok kanan bawah.');
    }

    public function download(string $file)
    {
        $this->authorizeSuperadmin();

        $relative = DatabaseBackup::safePath($file);
        abort_if($relative === null, 404, 'File backup tidak ditemukan.');

        $tmp = DatabaseBackup::downloadToTemp($relative);
        abort_if($tmp === null, 404, 'Gagal mengambil file backup dari storage.');

        $name = basename($relative);
        $headers = [];
        if (str_ends_with($name, '.zip')) {
            $headers['Content-Type'] = 'application/zip';
        }

        return response()->download($tmp, $name, $headers)->deleteFileAfterSend(true);
    }

    public function restore(Request $request)
    {
        $this->authorizeSuperadmin();

        $request->validate([
            'file' => ['nullable', 'file', 'max:512000'],
            'existing' => ['nullable', 'string'],
            'confirmation' => ['required', 'in:RESTORE'],
            'restore_code' => ['required', 'string'],
        ], [
            'confirmation.required' => 'Ketik RESTORE untuk konfirmasi.',
            'confirmation.in' => 'Konfirmasi tidak valid. Ketik RESTORE persis.',
            'restore_code.required' => 'Masukkan kode keamanan yang tampil di halaman backup.',
            'file.max' => 'Ukuran file terlalu besar (maks 500MB).',
        ]);

        // Langkah kedua konfirmasi: kode acak dari sesi (dibuat saat halaman
        // dibuka). Tolak bila kosong/salah, lalu hanguskan agar tidak bisa
        // dipakai ulang (one-time use).
        $sessionCode = session('restore_code');
        if (! $sessionCode || ! hash_equals($sessionCode, (string) $request->input('restore_code'))) {
            return back()->with('error', 'Kode keamanan tidak cocok. Buka kembali halaman backup untuk mendapatkan kode terbaru.');
        }

        session()->forget('restore_code');

        $filePath = null;
        $backupRelative = null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            if (! $uploadedFile->isValid()) {
                return back()->with('error', 'File gagal diunggah. Silakan coba lagi.');
            }

            $source = $uploadedFile->getClientOriginalName();
            $extension = strtolower($uploadedFile->getClientOriginalExtension());

            if (! in_array($extension, ['zip', 'sql'])) {
                return back()->with('error', 'Format file tidak valid. Hanya file .zip atau .sql yang diterima.');
            }

            // Simpan ke folder persisten (bukan temp sistem) agar file tetap ada
            // saat job queue memprosesnya nanti.
            $dir = storage_path(self::RESTORE_UPLOAD_DIR);
            File::ensureDirectoryExists($dir);

            $filePath = $dir.'/'.uniqid('restore_', true).'.'.$extension;
            $uploadedFile->move($dir, basename($filePath));
        } elseif ($request->filled('existing')) {
            $backupRelative = DatabaseBackup::safePath($request->input('existing'));
            abort_if($backupRelative === null, 404, 'File backup tidak ditemukan.');

            $source = basename($request->input('existing'));
        } else {
            return back()->with('error', 'Pilih file backup atau unggah file .zip/.sql untuk restore.');
        }

        if (! BackupProgress::start('restore', $source)) {
            if ($filePath && is_file($filePath)) {
                @unlink($filePath);
            }

            return back()->with('error', 'Masih ada proses backup/restore yang berjalan. Tunggu hingga selesai atau batalkan terlebih dahulu.');
        }

        RunRestoreJob::dispatch(
            auth()->id(),
            $source,
            filePath: $filePath,
            backupRelative: $backupRelative,
        );

        return back()->with('info', 'Restore sedang berjalan di latar belakang. Anda bebas berpindah halaman — progres tampil di pojok kanan bawah.');
    }

    /**
     * State progres task backup/restore (dipoll widget global).
     */
    public function progress()
    {
        $this->authorizeSuperadmin();

        return response()->json(BackupProgress::state() ?? ['status' => 'idle']);
    }

    /**
     * Minta pembatalan task yang sedang berjalan (dipenuhi secara kooperatif).
     */
    public function cancel()
    {
        $this->authorizeSuperadmin();

        return response()->json(['ok' => BackupProgress::requestCancel()]);
    }

    public function destroy(string $file)
    {
        $this->authorizeSuperadmin();

        $relative = DatabaseBackup::safePath($file);
        abort_if($relative === null, 404, 'File backup tidak ditemukan.');

        // Hapus lewat service terpusat agar versi lama objek di B2
        // (versioning: delete biasa hanya membuat hide marker) ikut ter-purge.
        app(FileUploadService::class)->deletePath($relative, DatabaseBackup::diskName());

        ActivityLogger::log('deleted', 'Hapus backup: '.basename($file), 'system');

        return back()->with('success', 'Backup '.basename($file).' berhasil dihapus.');
    }

    /**
     * Hapus banyak file backup sekaligus (dipilih via checkbox di halaman index).
     */
    public function destroyMany(Request $request)
    {
        $this->authorizeSuperadmin();

        $files = array_values(array_unique(array_filter((array) $request->input('files', []))));

        if (empty($files)) {
            return back()->with('error', 'Pilih minimal satu file backup untuk dihapus.');
        }

        $deleted = 0;
        $failed = [];

        foreach ($files as $file) {
            $relative = DatabaseBackup::safePath($file);
            if ($relative === null) {
                $failed[] = $file;
                continue;
            }

            try {
                app(FileUploadService::class)->deletePath($relative, DatabaseBackup::diskName());
                $deleted++;
            } catch (\Throwable $e) {
                $failed[] = $file;
            }
        }

        ActivityLogger::log('deleted', 'Hapus '.count($files).' backup (berhasil '.$deleted.')', 'system');

        if ($deleted > 0 && empty($failed)) {
            return back()->with('success', $deleted.' file backup berhasil dihapus.');
        }

        if ($deleted > 0) {
            return back()->with('warning', $deleted.' file dihapus; '.count($failed).' gagal: '.implode(', ', $failed));
        }

        return back()->with('error', 'Gagal menghapus file backup: '.implode(', ', $failed));
    }
}
