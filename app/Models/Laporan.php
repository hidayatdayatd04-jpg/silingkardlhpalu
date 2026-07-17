<?php

namespace App\Models;

use App\Enums\Bidang;
use App\Enums\PengaduanStatus;
use App\Enums\StatusPengaduanRth;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
    protected $table = 'laporans';

    protected $fillable = [
        'bidang',
        'nomor_tiket',
        'nama_pelapor',
        'nomor_hp',
        'email',
        'kategori',
        'jenis_pengaduan',
        'deskripsi',
        'alamat',
        'latitude',
        'longitude',
        'status',
        'catatan_admin',
        'alasan_penolakan',
        'bukti_foto_selesai',
    ];

    protected function casts(): array
    {
        return [
            'bidang' => Bidang::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->bidang)) {
                $model->bidang = Bidang::RTH->value;
            }

            if (empty($model->status)) {
                $model->status = $model->bidang === Bidang::RTH->value
                    ? StatusPengaduanRth::BELUM_DITINJAU->value
                    : PengaduanStatus::BELUM_DITINDAKLANJUTI->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generate(
                    $model->bidang,
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    public function scopeForBidang(Builder $query, Bidang|string $bidang): Builder
    {
        $value = $bidang instanceof Bidang ? $bidang->value : $bidang;

        return $query->where('bidang', $value);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(LaporanFoto::class, 'laporan_id');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->bidang?->value ?? $this->attributes['bidang'] ?? '') {
            'rth' => match ($this->attributes['status'] ?? '') {
                'Belum Ditinjau' => 'Belum Ditinjau',
                'Ditinjau' => 'Ditinjau',
                'Selesai' => 'Selesai',
                'Ditolak' => 'Ditolak',
                default => $this->attributes['status'] ?? '-',
            },
            default => match ($this->attributes['status'] ?? '') {
                'Belum Ditindaklanjuti' => 'Belum Ditindaklanjuti',
                'Ditindaklanjuti' => 'Ditindaklanjuti',
                default => $this->attributes['status'] ?? '-',
            },
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->attributes['status'] ?? '') {
            'Belum Ditinjau', 'Belum Ditindaklanjuti' => 'gray',
            'Ditinjau', 'Ditindaklanjuti' => 'amber',
            'Selesai' => 'success',
            'Ditolak' => 'danger',
            default => 'gray',
        };
    }
}
