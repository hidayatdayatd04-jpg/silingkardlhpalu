<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosialisasiFile extends Model
{
    protected $fillable = [
        'sosialisasi_id',
        'path',
        'tipe',
        'nama',
    ];

    public function sosialisasi(): BelongsTo
    {
        return $this->belongsTo(Sosialisasi::class);
    }
}
