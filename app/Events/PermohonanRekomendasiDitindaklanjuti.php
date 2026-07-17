<?php

namespace App\Events;

use App\Models\PermohonanRekomendasi;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermohonanRekomendasiDitindaklanjuti
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PermohonanRekomendasi $permohonanRekomendasi,
    ) {}
}
