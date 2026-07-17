<?php

namespace App\Listeners;

use App\Events\PermohonanRekomendasiDitindaklanjuti;
use Illuminate\Support\Facades\Log;

class SendPermohonanRekomendasiNotification
{
    public function handle(PermohonanRekomendasiDitindaklanjuti $event): void
    {
        $permohonan = $event->permohonanRekomendasi;

        Log::info('Permohonan rekomendasi ditindaklanjuti', [
            'nomor_tiket' => $permohonan->nomor_tiket,
            'email' => $permohonan->email,
            'nomor_telepon' => $permohonan->nomor_telepon,
        ]);

        // TODO: Kirim notifikasi email/WA ke pemohon setelah permohonan ditindaklanjuti.
    }
}
