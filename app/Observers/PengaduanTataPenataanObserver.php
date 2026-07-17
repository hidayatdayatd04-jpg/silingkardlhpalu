<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\PengaduanTataPenataan;

class PengaduanTataPenataanObserver
{
    public function updating(PengaduanTataPenataan $model): void
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
            PengaduanTataPenataan::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_pelapor ?? '',
            $model->nomor_tiket ?? '',
        );
    }

}
