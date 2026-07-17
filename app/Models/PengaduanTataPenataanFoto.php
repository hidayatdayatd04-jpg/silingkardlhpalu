<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanTataPenataanFoto extends Model
{
    protected $fillable = [
        'pengaduan_tata_penataan_id',
        'path_foto',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(PengaduanTataPenataan::class, 'pengaduan_tata_penataan_id');
    }
}
