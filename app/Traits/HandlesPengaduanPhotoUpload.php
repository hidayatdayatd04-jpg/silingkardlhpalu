<?php

namespace App\Traits;

use App\Jobs\ProcessPhotoUpload;
use App\Models\LaporanFoto;
use App\Models\PengaduanTataPenataanFoto;
use Illuminate\Http\UploadedFile;
use Livewire\Component;

/**
 * Shared logic for pengaduan Livewire components to offload image processing
 * to a queued job while showing a temporary "processing" status in the UI.
 */
trait HandlesPengaduanPhotoUpload
{
    public ?string $ticket = null;
    public bool $processing = false;
    public ?string $photoError = null;
    public array $processingFotoIds = [];
    public string $processingFotoType = 'laporan';

    /**
     * Stage each uploaded file locally, create a pending foto row and dispatch
     * a queued job to optimise + upload it to B2.
     */
    protected function queuePhotos(
        array $photos,
        int $parentId,
        string $fkColumn,
        string $fotoModelClass,
        string $context,
        string $fotoType,
    ): void {
        foreach ($photos as $photo) {
            /** @var UploadedFile $photo */
            $stagingPath = $photo->store('pending-photos', 'local');

            $foto = $fotoModelClass::create([
                $fkColumn => $parentId,
                'status' => 'pending',
                'staging_path' => $stagingPath,
            ]);

            ProcessPhotoUpload::dispatch($fotoType, $foto->id, $stagingPath, $context);

            $this->processingFotoIds[] = $foto->id;
        }

        if (count($photos) > 0) {
            $this->processingFotoType = $fotoType;
        }
    }

    /**
     * Polled from the browser: flips processing -> done once all photos finish.
     */
    public function checkPhotoStatus(): void
    {
        if (!$this->processing) {
            return;
        }

        $model = $this->processingFotoType === 'tata'
            ? PengaduanTataPenataanFoto::class
            : LaporanFoto::class;

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
}
