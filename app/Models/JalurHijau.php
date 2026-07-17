<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalurHijau extends Model
{
    protected $fillable = [
        'nama_ruas',
        'koordinat',
        'panjang',
    ];

    protected function casts(): array
    {
        return [
            'koordinat' => 'array',
            'panjang' => 'decimal:2',
        ];
    }
}
