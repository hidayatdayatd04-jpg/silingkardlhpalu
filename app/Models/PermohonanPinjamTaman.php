<?php

namespace App\Models;

use App\Enums\StatusPengaduanRth;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;

class PermohonanPinjamTaman extends Model
{
    protected $table = 'permohonan_pinjam_taman';

    protected $fillable = [
        'nomor_tiket',
        'nama_pemohon',
        'nomor_hp',
        'email',
        'nama_kegiatan',
        'nama_taman',
        'tanggal_kegiatan',
        'tanggal_selesai',
        'surat_permohonan',
        'jaminan_kebersihan',
        'surat_jaminan',
        'status',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengaduanRth::class,
            'tanggal_kegiatan' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'jaminan_kebersihan' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = StatusPengaduanRth::BELUM_DITINJAU->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    TicketGenerator::prefixForModel('permohonan_pinjam_taman'),
                    static::class,
                );
            }
        });
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }

    public static function hasConflict(string $namaTaman, \DateTimeInterface $start, ?\DateTimeInterface $end = null): bool
    {
        $end ??= $start;

        return static::query()
            ->where('nama_taman', $namaTaman)
            ->where('status', StatusPengaduanRth::DITINJAU->value)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('tanggal_kegiatan', [$start, $end])
                    ->orWhereBetween('tanggal_selesai', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('tanggal_kegiatan', '<=', $start)
                            ->where('tanggal_selesai', '>=', $end);
                    });
            })
            ->exists();
    }
}
