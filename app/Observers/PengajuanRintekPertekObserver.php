<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\PengajuanRintekPertek;

class PengajuanRintekPertekObserver
{
    public function updating(PengajuanRintekPertek $model): void
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
            PengajuanRintekPertek::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_perusahaan ?? '',
            $model->nomor_pengajuan ?? '',
        );
    }

}
