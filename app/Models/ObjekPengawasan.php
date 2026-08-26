<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjekPengawasan extends Model
{
    protected $table = 'objek_pengawasan';

    protected $fillable = [
        'nama_perusahaan',
        'nama_penanggung_jawab',
        'jenis_usaha',
        'alamat',
        'latitude',
        'longitude',
        'no_hp',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(ObjekPengawasanDokumen::class);
    }

    public function sidaks(): HasMany
    {
        return $this->hasMany(Sidak::class);
    }

    public function sosialisasiPesertas(): HasMany
    {
        return $this->hasMany(SosialisasiPeserta::class);
    }
}
