<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sosialisasi extends Model
{
    protected $fillable = [
        'judul',
        'jenis_kegiatan',
        'periode_tw',
        'tahun',
        'tanggal',
        'materi',
        'hasil_evaluasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function isMonitoringEvaluasi(): bool
    {
        return ($this->jenis_kegiatan ?? 'sosialisasi') === 'monitoring-evaluasi';
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(SosialisasiPeserta::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SosialisasiFile::class);
    }
}
