<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\PermohonanPinjamTaman;

class PermohonanPinjamTamanObserver
{
    public function updating(PermohonanPinjamTaman $model): void
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
            PermohonanPinjamTaman::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_pemohon ?? '',
            $model->nomor_tiket ?? '',
        );
    }

}
