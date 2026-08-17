<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermohonanDokumen extends Model
{
    protected $table = 'permohonan_dokumen';

    protected $fillable = [
        'permohonan_rekomendasi_id',
        'path_dokumen',
        'nama_dokumen',
    ];

    public function permohonanRekomendasi(): BelongsTo
    {
        return $this->belongsTo(PermohonanRekomendasi::class, 'permohonan_rekomendasi_id');
    }
}
