<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\RegistrasiUsahaLb3;

class RegistrasiUsahaLb3Observer
{
    public function updating(RegistrasiUsahaLb3 $model): void
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
            RegistrasiUsahaLb3::class,
            $model->id,
            $statusLama ?? '',
            $statusBaru,
            $model->email ?? '',
            $model->nama_perusahaan ?? '',
            $model->nomor_registrasi ?? '',
        );
    }

}
