<?php

namespace App\Models;

use App\Enums\StatistikSampahPeriode;
use Illuminate\Database\Eloquent\Model;

class StatistikSampah extends Model
{
    protected $fillable = [
        'tanggal',
        'volume_ton',
        'periode',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'volume_ton' => 'decimal:2',
            'periode' => StatistikSampahPeriode::class,
        ];
    }
}
