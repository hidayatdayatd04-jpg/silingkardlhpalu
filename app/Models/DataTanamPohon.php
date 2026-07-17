<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataTanamPohon extends Model
{
    protected $fillable = [
        'nama_penanggung_jawab',
        'jumlah_pohon',
        'jenis_pohon',
        'latitude',
        'longitude',
        'foto_dokumentasi',
        'perizinan_tebang_pohon_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'foto_dokumentasi' => 'array',
        ];
    }

    public function perizinanTebangPohon(): BelongsTo
    {
        return $this->belongsTo(PerizinanTebangPohon::class);
    }
}
