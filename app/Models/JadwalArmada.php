<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalArmada extends Model
{
    protected $table = 'jadwal_armada';

    protected $fillable = [
        'nama_rute',
        'hari',
        'jam',
        'wilayah_dilalui',
    ];
}
