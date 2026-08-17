<?php

namespace App\Traits;

use App\Jobs\ProcessPhotoUpload;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Shared logic for pengaduan Livewire components to process uploaded photos
 * (optimise + upload to B2) synchronously at submit time, while keeping a
 * temporary "processing" status in the UI as a safety net.
 */
trait HandlesPengaduanPhotoUpload
{
    public ?string $ticket = null;
    public bool $processing = false;
    public ?string $photoError = null;
    public array $processingFotoIds = [];
    /** @var class-string|null Kelas model foto yang sedang diproses. */
    public ?string $processingFotoModel = null;

    /**
     * Process each uploaded file directly (synchronously) at submit time:
     * stage locally, create a pending foto row, then optimise + upload it to
     * B2 right away. No queue worker is needed — the queued job class is only
     * reused as a plain processor here.
     */
    protected function processPhotos(
        array $photos,
        int $parentId,
        string $fkColumn,
        string $fotoModelClass,
        string $context,
    ): void {
        $service = app(ImageUploadService::class);

        foreach ($photos as $photo) {
            /** @var UploadedFile $photo */
            // Simpan staging dengan NAMA ASLI file (karakter berbahaya dibersihkan).
            // Bila sudah ada file dengan nama yang sama, ditambahkan sufiks -1, -2, dst.
            $stagingPath = $photo->storeAs('pending-photos', $this->uniqueStagingName($photo), 'local');

            // Hapus file sementara Livewire setelah staging agar tidak tersisa
            // sebagai salinan ganda / yatim di storage (terutama bila disk
            // sementara bukan 'local').
            try {
                if ($photo instanceof TemporaryUploadedFile) {
                    $photo->delete();
                }
            } catch (Throwable $e) {
                // Abaikan — pembersihan berkala 24 jam tetap berjalan.
            }

            $foto = $fotoModelClass::create([
                $fkColumn => $parentId,
                'status' => 'pending',
                'staging_path' => $stagingPath,
            ]);

            // Proses langsung di request ini (tanpa queue) agar tidak mangkrak
            // bila tidak ada queue worker yang berjalan.
            $job = new ProcessPhotoUpload($fotoModelClass, $foto->id, $stagingPath, $context);

            try {
                $job->handle($service);
            } catch (Throwable $e) {
                $job->failed($e);
            }

            $this->processingFotoIds[] = $foto->id;
        }

        if (count($photos) > 0) {
            $this->processingFotoModel = $fotoModelClass;
        }
    }

    /**
     * Polled from the browser: flips processing -> done once all photos finish.
     */
    public function checkPhotoStatus(): void
    {
        if (!$this->processing || !$this->processingFotoModel) {
            return;
        }

        $model = $this->processingFotoModel;

        $rows = $model::whereIn('id', $this->processingFotoIds)->get();

        if ($rows->isEmpty()) {
            $this->processing = false;

            return;
        }

        if ($rows->contains('status', 'failed')) {
            $this->photoError = __('Beberapa foto gagal diproses. Pengaduan Anda tetap tersimpan, namun sebagian foto mungkin tidak tampil. Silakan hubungi admin.');
            $this->processing = false;

            return;
        }

        if ($rows->every(fn ($row) => $row->status === 'done')) {
            $this->processing = false;
        }
    }

    public function resetPhotoState(): void
    {
        $this->processing = false;
        $this->photoError = null;
        $this->processingFotoIds = [];
        $this->ticket = null;
    }

    /**
     * Nama staging unik dengan nama asli file (ekstensi asli dipertahankan).
     * Karakter yang berbahaya bagi filesystem/URL dibersihkan; bila nama sudah
     * dipakai file lain di folder staging, ditambahkan sufiks -1, -2, dst.
     */
    protected function uniqueStagingName(UploadedFile $photo): string
    {
        $original = str_replace('\\', '/', (string) $photo->getClientOriginalName());
        $original = basename($original);
        $original = str_replace(chr(0), '', $original);

        $extension = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
        $base = (string) pathinfo($original, PATHINFO_FILENAME);

        $base = preg_replace('~[<>:"/\\\\|?*#%\r\n\t]~', '-', $base) ?? '';
        $base = trim($base, " \t.-_");

        if ($base === '') {
            $base = 'foto';
        }

        $filename = $extension === '' ? $base : $base.'.'.$extension;

        $storage = \Illuminate\Support\Facades\Storage::disk('local');
        $suffix = 1;

        while ($storage->exists('pending-photos/'.$filename)) {
            $filename = $base.'-'.$suffix.($extension === '' ? '' : '.'.$extension);
            $suffix++;
        }

        return $filename;
    }
}
