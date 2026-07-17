<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TamanKota extends Model
{
    protected $fillable = [
        'nama',
        'latitude',
        'longitude',
        'luas',
        'foto',
        'fasilitas',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'luas' => 'decimal:2',
        ];
    }

    public function permohonanPinjamTamans(): HasMany
    {
        return $this->hasMany(PermohonanPinjamTaman::class);
    }
}
