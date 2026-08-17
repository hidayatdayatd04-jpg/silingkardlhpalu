<?php

namespace App\Models;

use App\Enums\PengaduanStatus;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PengaduanPengendalian extends Model
{
    protected $table = 'pengaduan_pengendalian';

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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = PengaduanStatus::BELUM_DITINDAKLANJUTI->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    'PDL',
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PengaduanPengendalianFoto::class, 'pengaduan_pengendalian_id');
    }

    public function feedback(): MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status'] ?? '') {
            'Belum Ditindaklanjuti' => 'Belum Ditindaklanjuti',
            'Ditindaklanjuti' => 'Ditindaklanjuti',
            default => $this->attributes['status'] ?? '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->attributes['status'] ?? '') {
            'Belum Ditindaklanjuti' => 'gray',
            'Ditindaklanjuti' => 'amber',
            default => 'gray',
        };
    }
}
