<?php

namespace App\Models;

use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\StatusPengaduan;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengaduanTataPenataan extends Model
{
    protected $table = 'pengaduan_tata_penataan';

    protected $fillable = [
        'nomor_tiket',
        'nama_pelapor',
        'nomor_hp',
        'jenis_pengaduan',
        'nama_terlapor',
        'nama_perusahaan_terlapor',
        'alamat',
        'latitude',
        'longitude',
        'deskripsi',
        'status',
        'catatan_admin',
        'assigned_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengaduan::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = StatusPengaduan::BELUM_DITINDAKLANJUTI;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    'TTP',
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PengaduanTataPenataanFoto::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function sidaks(): HasMany
    {
        return $this->hasMany(Sidak::class, 'pengaduan_tata_penataan_id');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
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
