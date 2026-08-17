<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataTanamPohon extends Model
{
    protected $table = 'data_tanam_pohon';

    protected $fillable = [
        'nama_penanggung_jawab',
        'jumlah_pohon',
        'jenis_pohon',
        'latitude',
        'longitude',
        'foto_dokumentasi',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'foto_dokumentasi' => 'array',
        ];
    }
}
