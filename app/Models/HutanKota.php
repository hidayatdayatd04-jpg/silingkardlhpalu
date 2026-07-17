<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutanKota extends Model
{
    protected $fillable = [
        'nama',
        'latitude',
        'longitude',
        'luas',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'luas' => 'decimal:2',
        ];
    }
}
