<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PohonPelindung extends Model
{
    protected $fillable = [
        'jenis_pohon',
        'latitude',
        'longitude',
        'tahun_tanam',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}
