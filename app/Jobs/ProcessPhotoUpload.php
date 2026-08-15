<?php

namespace App\Jobs;

use App\Models\LaporanFoto;
use App\Models\PengaduanTataPenataanFoto;
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
     * @param  string  $fotoType     'laporan' | 'tata'
     * @param  int     $fotoId        Primary key of the foto row.
     * @param  string  $stagingPath  Local disk path of the temporarily stored upload.
     * @param  string  $context      Storage folder, e.g. "pengaduan-pengendalian".
     */
    public function __construct(
        public string $fotoType,
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
        $paths = $service->upload($source, $this->context);

        $foto->update([
            'path_foto' => $paths['full'],
            'thumb_path' => $paths['thumb'],
            'medium_path' => $paths['medium'],
            'full_path' => $paths['full'],
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

        Storage::disk('local')->delete($this->stagingPath);

        Log::error('ProcessPhotoUpload failed', [
            'fotoType' => $this->fotoType,
            'fotoId' => $this->fotoId,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function resolveFoto()
    {
        return $this->fotoType === 'tata'
            ? PengaduanTataPenataanFoto::findOrFail($this->fotoId)
            : LaporanFoto::findOrFail($this->fotoId);
    }
}
