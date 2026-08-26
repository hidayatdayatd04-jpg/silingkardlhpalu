<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pelanggaran extends Model
{
    protected $fillable = [
        'sidak_id',
        'sidak_manual',
        'jenis_pelanggaran',
        'pasal_dilanggar',
        'keterangan',
    ];

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

    /**
     * Ringkasan Sidak untuk detail Pelanggaran. Nilai manual diprioritaskan
     * ketika Pelanggaran belum dapat dihubungkan ke record Sidak di sistem.
     */
    public function getSidakTerkaitTextAttribute(): ?string
    {
        if (filled($this->sidak_manual)) {
            return $this->sidak_manual;
        }

        $sidak = $this->sidak;
        if (! $sidak) {
            return null;
        }

        $tanggal = $sidak->tanggal_sidak?->format('d M Y');
        $hasil = $sidak->hasil_label ?? $sidak->hasil;

        return collect([$tanggal, $hasil])->filter()->implode(' — ') ?: 'Sidak #'.$sidak->getKey();
    }
}
