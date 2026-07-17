<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidakMedia extends Model
{
    protected $fillable = [
        'sidak_id',
        'path',
        'tipe',
    ];

    public function sidak(): BelongsTo
    {
        return $this->belongsTo(Sidak::class);
    }
}
