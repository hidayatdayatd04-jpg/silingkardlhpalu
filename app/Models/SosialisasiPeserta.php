<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosialisasiPeserta extends Model
{
    protected $fillable = [
        'sosialisasi_id',
        'objek_pengawasan_id',
        'sertifikat_path',
    ];

    public function sosialisasi(): BelongsTo
    {
        return $this->belongsTo(Sosialisasi::class);
    }

    public function objekPengawasan(): BelongsTo
    {
        return $this->belongsTo(ObjekPengawasan::class);
    }
}
