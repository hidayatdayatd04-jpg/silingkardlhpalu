<?php

namespace App\Models;

use App\Enums\JenisSanksi;
use App\Enums\StatusSanksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanksi extends Model
{
    protected $fillable = [
        'pelanggaran_id',
        'jenis_sanksi',
        'batas_waktu_perbaikan',
        'status_sanksi',
        'surat_path',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jenis_sanksi' => JenisSanksi::class,
            'status_sanksi' => StatusSanksi::class,
            'batas_waktu_perbaikan' => 'date',
        ];
    }

    public function pelanggaran(): BelongsTo
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function objekPengawasan()
    {
        return $this->hasOneThrough(
            ObjekPengawasan::class,
            Pelanggaran::class,
            'id',        // Foreign key on pelanggarans table
            'id',        // Foreign key on objek_pengawasans table
            'pelanggaran_id', // Local key on sanksis table
            'objek_pengawasan_id' // Local key on pelanggarans table
        );
    }

    public function isOverdue(): bool
    {
        if (! $this->batas_waktu_perbaikan) {
            return false;
        }

        return $this->batas_waktu_perbaikan->isPast()
            && $this->status_sanksi !== StatusSanksi::SELESAI;
    }

    /**
     * Nama objek pengawasan (lewat relasi pelanggaran) — untuk export.
     */
    public function getObjekPengawasanNamaAttribute(): ?string
    {
        return $this->pelanggaran?->objekPengawasan?->nama_perusahaan;
    }

    /**
     * Jenis pelanggaran terkait — untuk export.
     */
    public function getJenisPelanggaranTextAttribute(): ?string
    {
        return $this->pelanggaran?->jenis_pelanggaran;
    }
}
