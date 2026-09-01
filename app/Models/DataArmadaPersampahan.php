<?php

namespace App\Models;

use App\Enums\KategoriArmadaPersampahan;
use Illuminate\Database\Eloquent\Model;

class DataArmadaPersampahan extends Model
{
    protected $table = 'data_armada_persampahan';

    protected $fillable = [
        'kategori',
        'jenis_kendaraan',
        'merk_type',
        'tahun_perolehan',
        'nomor_polisi',
        'jumlah',
        'kondisi',
        'keterangan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => KategoriArmadaPersampahan::class,
            'jumlah' => 'integer',
        ];
    }
}
