<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalArmada extends Model
{
    protected $fillable = [
        'nama_rute',
        'hari',
        'jam',
        'wilayah_dilalui',
    ];
}
