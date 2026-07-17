<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisUsaha extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function permohonanRekomendasis(): HasMany
    {
        return $this->hasMany(PermohonanRekomendasi::class, 'jenis_usaha_id');
    }
}
