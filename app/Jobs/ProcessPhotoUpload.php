<?php

namespace App\Jobs;

use App\Services\ImageUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPhotoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param  class-string  $fotoModelClass  Kelas model foto (mis. PengaduanRthFoto).
     * @param  int           $fotoId          Primary key of the foto row.
     * @param  string        $stagingPath     Local disk path of the temporarily stored upload.
     * @param  string        $context         Storage folder, e.g. "pengaduan-pengendalian".
     */
    public function __construct(
        public string $fotoModelClass,
        public int $fotoId,
        public string $stagingPath,
        public string $context,
    ) {
    }

    public function handle(ImageUploadService $service): void
    {
        $foto = $this->resolveFoto();
        $foto->update(['status' => 'processing', 'error_message' => null]);

        $source = Storage::disk('local')->path($this->stagingPath);
        $path = $service->upload($source, $this->context);

        // Satu file WebP dengan nama asli — tanpa varian thumb/medium/full.
        $foto->update([
            'path_foto' => $path,
            'status' => 'done',
            'error_message' => null,
        ]);

        Storage::disk('local')->delete($this->stagingPath);
    }

    public function failed(Throwable $exception): void
    {
        $foto = $this->resolveFoto();
        $foto->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        // File staging SENGAJA dipertahankan agar proses dapat diulang
        // lewat "php artisan dlh:photos:retry-failed" setelah kendala di
        // server (mis. encoder gambar) diperbaiki. Staging hanya dihapus
        // pada keberhasilan di handle().

        Log::error('ProcessPhotoUpload failed', [
            'fotoModel' => $this->fotoModelClass,
            'fotoId' => $this->fotoId,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function resolveFoto()
    {
        return $this->fotoModelClass::findOrFail($this->fotoId);
    }
}
