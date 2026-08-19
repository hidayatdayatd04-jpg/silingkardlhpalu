<?php

namespace App\Models;

use App\Enums\StatusPengaduan;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;

class PermohonanPinjamTaman extends Model
{
    protected $table = 'permohonan_pinjam_taman';

    protected $fillable = [
        'nomor_tiket',
        'nama_pemohon',
        'nomor_hp',
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
            'status' => StatusPengaduan::class,
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
                $model->status = StatusPengaduan::BELUM_DITINDAKLANJUTI->value;
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

    public static function hasConflict(string $namaTaman, \DateTimeInterface|string $start, \DateTimeInterface|string|null $end = null): bool
    {
        $startDate = \Illuminate\Support\Carbon::parse($start)->startOfDay();
        $endDate = $end ? \Illuminate\Support\Carbon::parse($end)->endOfDay() : $startDate->copy()->endOfDay();

        return static::query()
            ->where('nama_taman', $namaTaman)
            ->whereIn('status', [
                StatusPengaduan::BELUM_DITINDAKLANJUTI->value,
                StatusPengaduan::DITINDAKLANJUTI->value,
            ])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('tanggal_kegiatan', '<=', $endDate)
                    ->where(function ($q) use ($startDate) {
                        $q->where('tanggal_selesai', '>=', $startDate)
                            ->orWhere(function ($q2) use ($startDate) {
                                $q2->whereNull('tanggal_selesai')
                                    ->where('tanggal_kegiatan', '>=', $startDate);
                            });
                    });
            })
            ->exists();
    }
}
