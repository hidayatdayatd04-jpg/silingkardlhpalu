<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\PerizinanTebangPohon;

class PerizinanTebangPohonObserver
{
    public function updating(PerizinanTebangPohon $model): void
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
            PerizinanTebangPohon::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_pemohon ?? '',
            $model->nomor_tiket ?? '',
        );
    }

}
