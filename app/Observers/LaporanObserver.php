<?php

namespace App\Observers;

use App\Jobs\SendEmailNotificationJob;
use App\Models\Laporan;

class LaporanObserver
{
    public function updating(Laporan $model): void
    {
        if (!$model->isDirty('status')) {
            return;
        }

        $statusLama = $model->getOriginal('status');
        $statusBaru = $model->status;

        // Cast enum to string for the job
        $statusLamaStr = $statusLama instanceof \BackedEnum ? $statusLama->value : (string) ($statusLama ?? '');
        $statusBaruStr = $statusBaru instanceof \BackedEnum ? $statusBaru->value : (string) ($statusBaru ?? '');

        if ($statusLamaStr === $statusBaruStr) {
            return;
        }

        SendEmailNotificationJob::dispatch(
            Laporan::class,
            $model->id,
            $statusLamaStr,
            $statusBaruStr,
            $model->email ?? '',
            $model->nama_pelapor ?? '',
            $model->nomor_tiket ?? '',
        );
    }

}
