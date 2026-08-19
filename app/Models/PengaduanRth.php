<?php

namespace App\Models;

use App\Enums\StatusPengaduan;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PengaduanRth extends Model
{
    protected $table = 'pengaduan_rth';

    protected $fillable = [
        'nomor_tiket',
        'nama_pelapor',
        'nomor_hp',
        'jenis_pengaduan',
        'deskripsi',
        'alamat',
        'latitude',
        'longitude',
        'status',
        'catatan_admin',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengaduan::class,
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
                    'RTH',
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PengaduanRthFoto::class, 'pengaduan_rth_id');
    }

    public function feedback(): MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? ($this->attributes['status'] ?? '-');
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }
}
