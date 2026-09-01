<?php

namespace App\Models;

use App\Enums\KategoriArmadaPersampahan;
use Illuminate\Database\Eloquent\Model;

class DataArmadaPersampahan extends Model
{
    protected $table = 'data_armada_persampahan';

    protected $fillable = [
        'kategori',
        'merk_type',
        'tahun_perolehan',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => KategoriArmadaPersampahan::class,
        ];
    }
}
