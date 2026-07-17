<?php

namespace App\Models;

use App\Enums\HasilSidak;
use App\Enums\StatusTindakLanjutSidak;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sidak extends Model
{
    protected $fillable = [
        'objek_pengawasan_id',
        'pengaduan_tata_penataan_id',
        'tanggal_sidak',
        'nama_petugas',
        'user_id',
        'hasil',
        'temuan',
        'rekomendasi',
        'status_tindak_lanjut',
        'is_jadwal',
        'catatan_jadwal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sidak' => 'date',
            'status_tindak_lanjut' => StatusTindakLanjutSidak::class,
            'is_jadwal' => 'boolean',
        ];
    }

    // Helper to get hasil label (supports both enum and custom string values)
    public function getHasilLabelAttribute(): ?string
    {
        $hasil = $this->attributes['hasil'] ?? null;
        if ($hasil === null) {
            return null;
        }

        $enum = HasilSidak::tryFrom($hasil);
        return $enum ? $enum->label() : $hasil;
    }

    // Helper to get hasil color (supports both enum and custom string values)
    public function getHasilColorAttribute(): ?string
    {
        $hasil = $this->attributes['hasil'] ?? null;
        if ($hasil === null) {
            return null;
        }

        $enum = HasilSidak::tryFrom($hasil);
        return $enum ? $enum->color() : 'info';
    }

    public function objekPengawasan(): BelongsTo
    {
        return $this->belongsTo(ObjekPengawasan::class);
    }

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(PengaduanTataPenataan::class, 'pengaduan_tata_penataan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(SidakMedia::class);
    }

    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
