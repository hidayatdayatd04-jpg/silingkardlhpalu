<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pelanggaran extends Model
{
    protected $fillable = [
        'objek_pengawasan_id',
        'sidak_id',
        'jenis_pelanggaran',
        'pasal_dilanggar',
        'keterangan',
    ];

    public function objekPengawasan(): BelongsTo
    {
        return $this->belongsTo(ObjekPengawasan::class);
    }

    public function sidak(): BelongsTo
    {
        return $this->belongsTo(Sidak::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PelanggaranMedia::class);
    }

    public function sanksi(): HasOne
    {
        return $this->hasOne(Sanksi::class);
    }

    public function getJenisSanksiTextAttribute(): ?string
    {
        return $this->sanksi?->jenis_sanksi?->label();
    }

    public function getStatusSanksiTextAttribute(): ?string
    {
        return $this->sanksi?->status_sanksi?->label();
    }
}
