<?php

namespace App\Models;

use App\Enums\StatusPengaduanRth;

class LaporanRth extends Laporan
{
    protected function casts(): array
    {
        $casts = parent::casts();

        $casts['status'] = StatusPengaduanRth::class;

        return $casts;
    }
}
