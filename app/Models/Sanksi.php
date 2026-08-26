<?php

namespace App\Models;

use App\Enums\JenisSanksi;
use App\Enums\StatusPengaduan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanksi extends Model
{
    protected $table = 'sanksi';

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
            'status_sanksi' => StatusPengaduan::class,
            'batas_waktu_perbaikan' => 'date',
        ];
    }

    public function pelanggaran(): BelongsTo
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function isOverdue(): bool
    {
        if (! $this->batas_waktu_perbaikan) {
            return false;
        }

        return $this->batas_waktu_perbaikan->isPast()
            && $this->status_sanksi !== StatusPengaduan::DITINDAKLANJUTI;
    }

    /**
     * Nama objek pengawasan diambil dari Sidak terkait bila tersedia.
     */
    public function getObjekPengawasanNamaAttribute(): ?string
    {
        return $this->pelanggaran?->sidak?->objekPengawasan?->nama_perusahaan;
    }

    /**
     * Jenis pelanggaran terkait — untuk export.
     */
    public function getJenisPelanggaranTextAttribute(): ?string
    {
        return $this->pelanggaran?->jenis_pelanggaran;
    }
}
