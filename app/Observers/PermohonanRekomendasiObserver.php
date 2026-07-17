<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\PermohonanRekomendasi;

class PermohonanRekomendasiObserver
{
    public function updating(PermohonanRekomendasi $model): void
    {
        if (!$model->isDirty('status')) {
            return;
        }

        $statusLama = $model->getOriginal('status');
        $statusBaru = $model->status;

        if ($statusLama === $statusBaru) {
            return;
        }

        SendEmailNotificationJob::dispatch(
            PermohonanRekomendasi::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_perusahaan ?? $model->nama_pemilik ?? '',
            $model->nomor_tiket ?? '',
        );
    }

}
