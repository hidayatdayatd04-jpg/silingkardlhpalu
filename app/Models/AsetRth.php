<?php

namespace App\Models;

use App\Enums\JenisAsetRth;
use App\Enums\KondisiAset;
use Illuminate\Database\Eloquent\Model;

class AsetRth extends Model
{
    protected $fillable = [
        'jenis_aset',
        'lokasi',
        'latitude',
        'longitude',
        'kondisi',
    ];

    protected function casts(): array
    {
        return [
            'jenis_aset' => JenisAsetRth::class,
            'kondisi' => KondisiAset::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}
